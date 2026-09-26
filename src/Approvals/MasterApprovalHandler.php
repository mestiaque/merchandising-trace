<?php

namespace ME\MerchandisingTrace\Approvals;

use App\Approvals\BaseApprovalHandler;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Handles 'merchandising.buyer' and 'merchandising.supplier' on the host's
 * central Approvals page (registered by MerchandisingServiceProvider).
 *
 * Recipients: the list in config('merchandising-trace.approval_recipients.<module>')
 * if one is set, otherwise every user holding '<permission prefix>.approve'
 * — the same rule the inventory package uses.
 */
class MasterApprovalHandler extends BaseApprovalHandler
{
    public function recipients(?Model $approvable, Approval $approval): array
    {
        $configured = array_values(array_filter(
            (array) (config('merchandising-trace.approval_recipients')[$approval->module] ?? [])
        ));
        if ($configured) {
            return $configured;
        }

        $permission = ($approvable && defined(get_class($approvable) . '::APPROVAL_PERMISSION'))
            ? $approvable::APPROVAL_PERMISSION . '.approve'
            : null;

        if (! $permission) {
            return [];
        }

        return User::all()
            ->filter(fn (User $user) => method_exists($user, 'hasPermission') && $user->hasPermission($permission))
            ->pluck('email')->filter()->values()->all();
    }

    public function onApproved(Approval $approval): void
    {
        $this->apply($approval, 'approved');
    }

    public function onRejected(Approval $approval): void
    {
        $this->apply($approval, 'rejected');
    }

    private function apply(Approval $approval, string $status): void
    {
        $record = $approval->approvable;

        if (! $record || ! method_exists($record, 'applyApprovalDecision') || ! $record->isPendingApproval()) {
            return;
        }

        $record->applyApprovalDecision($status, $approval->approved_by, $approval->approved_at, $approval->remarks);
    }
}
