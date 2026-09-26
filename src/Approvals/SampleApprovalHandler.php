<?php

namespace ME\MerchandisingTrace\Approvals;

use App\Approvals\BaseApprovalHandler;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Services\SampleApprovalService;

/**
 * 'merchandising.sample' on the host's central Approvals page. Approve /
 * Reject there does exactly what the sample page's buttons do
 * (SampleApprovalService): T&A write-back on approve, next revision on reject.
 */
class SampleApprovalHandler extends BaseApprovalHandler
{
    public function recipients(?Model $approvable, Approval $approval): array
    {
        $configured = array_values(array_filter(
            (array) (config('merchandising-trace.approval_recipients')[SampleApprovalService::MODULE] ?? [])
        ));
        if ($configured) {
            return $configured;
        }

        return User::all()
            ->filter(fn (User $user) => method_exists($user, 'hasPermission') && $user->hasPermission('merch_sample.approve'))
            ->pluck('email')->filter()->values()->all();
    }

    public function onApproved(Approval $approval): void
    {
        $sample = $this->decidable($approval);
        if ($sample) {
            app(SampleApprovalService::class)->approve($sample, $approval->remarks);
        }
    }

    public function onRejected(Approval $approval): void
    {
        $sample = $this->decidable($approval);
        if ($sample) {
            app(SampleApprovalService::class)->reject($sample, $approval->remarks ?: 'Rejected', $approval->approved_by);
        }
    }

    /** Only a sample still waiting for a decision (same states the sample page allows). */
    private function decidable(Approval $approval): ?Sample
    {
        // Merchandiser row-scope must not hide the sample from an approver.
        $sample = Sample::withoutGlobalScopes()->find($approval->approvable_id);

        return $sample && in_array($sample->status, ['submitted', 'in_progress'], true) ? $sample : null;
    }
}
