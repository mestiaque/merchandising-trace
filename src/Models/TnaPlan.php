<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TnaPlan extends Model
{
    use SoftDeletes;

    protected $table = 'mer_tna_plans';

    public const PCD_RESULTS = ['pending', 'pass', 'fail'];
    public const OVERALL_STATUSES = ['on_track', 'at_risk', 'delayed', 'completed'];

    protected $fillable = [
        'tna_no', 'sales_contract_po_id', 'tna_template_id', 'inquiry_given_date', 'merchandiser_id',
        'order_confirmation_due_date', 'factory_id', 'updated_date', 'pcd_result', 'pcd_fail_reason',
        'responsible_dept_id', 'responsible_person_id', 'overall_status', 'completion_percent', 'remarks',
    ];

    protected $casts = [
        'inquiry_given_date' => 'date',
        'order_confirmation_due_date' => 'date',
        'updated_date' => 'date',
        'completion_percent' => 'decimal:2',
    ];

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TnaTemplate::class, 'tna_template_id');
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }

    public function responsibleDept(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'responsible_dept_id');
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TnaTask::class, 'tna_plan_id')->orderBy('sequence');
    }

    /**
     * §8.3: days-to-ship countdown, row-level summary.
     */
    public function daysToShip(): ?int
    {
        $shipment = $this->salesContractPo?->effectiveShipment();

        return $shipment ? now()->diffInDays($shipment, false) : null;
    }

    public function recomputeCompletion(): void
    {
        $tasks = $this->tasks;
        $countable = $tasks->whereNotIn('status', ['na']);
        $done = $countable->whereIn('status', ['done', 'approved'])->count();
        $total = $countable->count();

        $this->completion_percent = $total > 0 ? round(($done / $total) * 100, 2) : 0;

        $overdue = $countable->contains(fn (TnaTask $t) => $t->isOverdue());
        $this->overall_status = $this->completion_percent >= 100
            ? 'completed'
            : ($overdue ? 'delayed' : ($countable->contains(fn (TnaTask $t) => $t->isDueSoon()) ? 'at_risk' : 'on_track'));

        $this->save();
    }
}
