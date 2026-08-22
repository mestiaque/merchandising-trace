<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\TnaAlert;
use ME\MerchandisingTrace\Models\TnaPlan;
use ME\MerchandisingTrace\Models\TnaTask;

/**
 * §8.6: daily job creating alerts for due-in-N-days, overdue, and
 * PCD-blocking-task-incomplete-within-7-days-of-PCD. In-app bell is just
 * "unread TnaAlert rows for the current user's plans"; the email digest is
 * a thin wrapper around the same query (left as a scheduled-command TODO
 * since this environment has no mail transport configured to verify against).
 */
class TnaAlertService
{
    public function runDaily(int $dueSoonDays = 3, int $pcdBlockingWindowDays = 7): int
    {
        $created = 0;

        TnaTask::query()
            ->whereNotIn('status', ['done', 'approved', 'na'])
            ->where('is_auto', false)
            ->whereNotNull('plan_date')
            ->chunkById(200, function ($tasks) use (&$created, $dueSoonDays) {
                foreach ($tasks as $task) {
                    if ($task->isOverdue()) {
                        $created += $this->raise($task, 'overdue');
                    } elseif ($task->isDueSoon($dueSoonDays)) {
                        $created += $this->raise($task, 'due_soon');
                    }
                }
            });

        TnaPlan::query()
            ->where('pcd_result', '!=', 'pass')
            ->with('salesContractPo')
            ->chunkById(200, function ($plans) use (&$created, $pcdBlockingWindowDays) {
                foreach ($plans as $plan) {
                    $pcd = $plan->salesContractPo?->effectivePcd();
                    if (! $pcd || now()->diffInDays($pcd, false) > $pcdBlockingWindowDays) {
                        continue;
                    }

                    foreach ($plan->tasks()->where('blocks_pcd', true)->whereNotIn('status', ['done', 'approved', 'na'])->get() as $task) {
                        $created += $this->raise($task, 'blocked_pcd');
                    }
                }
            });

        return $created;
    }

    private function raise(TnaTask $task, string $type): int
    {
        $alreadyRaisedToday = TnaAlert::query()
            ->where('tna_task_id', $task->id)
            ->where('alert_type', $type)
            ->whereDate('alert_date', now()->toDateString())
            ->exists();

        if ($alreadyRaisedToday) {
            return 0;
        }

        TnaAlert::create([
            'tna_task_id' => $task->id,
            'alert_type' => $type,
            'alert_date' => now(),
            'notified_to' => $task->plan?->merchandiser_id,
        ]);

        return 1;
    }
}
