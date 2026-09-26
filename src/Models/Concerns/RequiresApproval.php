<?php

namespace ME\MerchandisingTrace\Models\Concerns;

use App\Models\Approval;
use App\Services\ApprovalService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Master records (buyers, suppliers) that must be approved on the host's
 * central Approvals page before they can be used. A model using this trait
 * declares:
 *   const APPROVAL_MODULE = 'merchandising.buyer';   // key in config('approval.modules')
 *   const APPROVAL_PERMISSION = 'merch_buyer';       // '<prefix>.approve' may approve
 *
 * Only approved (and active) records reach dropdowns — scopeActive() in the
 * model checks both. The handler that applies the decision is
 * ME\MerchandisingTrace\Approvals\MasterApprovalHandler.
 */
trait RequiresApproval
{
    protected static function bootRequiresApproval(): void
    {
        // A deleted record can no longer be approved — withdraw its open request.
        static::deleting(function ($model) {
            if (static::approvalSystemAvailable()) {
                Approval::query()->pending()
                    ->where('approvable_type', static::class)->where('approvable_id', $model->getKey())
                    ->update(['status' => 'cancelled']);
            }
        });
    }

    /** False when the host app has no central approval system — records are then auto-approved. */
    public static function approvalSystemAvailable(): bool
    {
        return class_exists(ApprovalService::class) && class_exists(Approval::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('approval_status'), 'approved');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /** The open request on the central Approvals page, if any. */
    public function pendingApproval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable')->ofMany(['id' => 'max'], fn ($q) => $q->where('status', 'pending'));
    }

    /**
     * Mark the record pending and raise a request on the central Approvals
     * page (emailing the approvers). Safe to call again after a rejection.
     */
    public function submitForApproval(): void
    {
        if (! static::approvalSystemAvailable()) {
            $this->forceFill(['approval_status' => 'approved', 'approved_by' => null, 'approved_at' => null])->saveQuietly();

            return;
        }

        $this->forceFill([
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'approval_remarks' => null,
        ])->save();

        if ($this->pendingApproval()->exists()) {
            return;
        }

        $label = class_basename(static::class);

        app(ApprovalService::class)->request([
            'module' => static::APPROVAL_MODULE,
            'approvable' => $this,
            'title' => "New {$label} - {$this->name}" . ($this->code ? " ({$this->code})" : ''),
            'description' => "Merchandising {$label} \"{$this->name}\" needs approval before it can be used.",
            'route_name' => $this->approvalRouteName(),
            'route_params' => ['search' => $this->code ?: $this->name],
            'requested_by' => auth()->id(),
        ]);
    }

    /** Applied by the approval handler once the request is decided. */
    public function applyApprovalDecision(string $status, ?int $approvedBy, $approvedAt, ?string $remarks): void
    {
        $this->forceFill([
            'approval_status' => $status,
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt,
            'approval_remarks' => $remarks,
        ])->save();
    }

    abstract protected function approvalRouteName(): string;
}
