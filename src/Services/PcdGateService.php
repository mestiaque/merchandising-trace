<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\TnaPlan;

/**
 * §8.5: nightly job + on-demand evaluation, run per PO on its effective_pcd.
 * PASS only when every blocking task is done/approved/na; otherwise FAIL,
 * with reason + responsible dept/person computed from the earliest
 * incomplete blocking task — never typed by a user.
 */
class PcdGateService
{
    public function evaluate(TnaPlan $plan): TnaPlan
    {
        $blockingTasks = $plan->tasks()
            ->where('blocks_pcd', true)
            ->orderBy('sequence')
            ->get();

        $incomplete = $blockingTasks->reject(fn ($t) => in_array($t->status, ['done', 'approved', 'na'], true));

        if ($incomplete->isEmpty()) {
            $plan->update([
                'pcd_result' => 'pass',
                'pcd_fail_reason' => null,
                'responsible_dept_id' => null,
                'responsible_person_id' => null,
            ]);

            return $plan;
        }

        $earliest = $incomplete->first();

        $plan->update([
            'pcd_result' => 'fail',
            'pcd_fail_reason' => $incomplete->pluck('task_name')->implode(', '),
            'responsible_dept_id' => $earliest->responsible_dept_id,
            'responsible_person_id' => $earliest->responsible_person_id,
        ]);

        return $plan;
    }

    /**
     * Manual override — requires merch_tna.override_pcd permission at the
     * controller layer + a mandatory reason, logged via TnaTaskLog against
     * a synthetic "pcd_override" entry on the plan's blocking tasks.
     */
    public function override(TnaPlan $plan, string $reason, int $userId): TnaPlan
    {
        $plan->update(['pcd_result' => 'pass', 'pcd_fail_reason' => "Overridden: {$reason}"]);

        foreach ($plan->tasks()->where('blocks_pcd', true)->get() as $task) {
            $task->logs()->create([
                'field' => 'pcd_override',
                'old_value' => 'fail',
                'new_value' => 'pass',
                'changed_by' => $userId,
                'changed_at' => now(),
                'reason' => $reason,
            ]);
        }

        return $plan;
    }
}
