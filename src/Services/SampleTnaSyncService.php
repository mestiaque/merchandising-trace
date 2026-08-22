<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\TnaTask;

/**
 * §8.4: Sample Status / Wash Status auto-sync — approving (or submitting) a
 * sample writes back into every matching, currently-open T&A task across all
 * plans for that style, keyed by auto_source_ref = the sample's type code.
 * Auto-filled cells stay read-only in the UI/API (TnaPlanController rejects
 * manual edits when is_auto is true).
 */
class SampleTnaSyncService
{
    /** task_code suffix => Sample date field it mirrors */
    private const FIELD_MAP = [
        'request' => 'request_date',
        'submission' => 'submit_date',
        'submit' => 'submit_date',
        'approval' => 'approval_date',
    ];

    public function syncFromSample(Sample $sample): int
    {
        if (! $sample->sampleType) {
            return 0;
        }

        $tasks = TnaTask::query()
            ->where('auto_source', 'sample')
            ->where('auto_source_ref', $sample->sampleType->code)
            ->whereHas('plan.salesContractPo', fn ($q) => $q->where('style_id', $sample->style_id))
            ->get();

        $synced = 0;

        foreach ($tasks as $task) {
            $suffix = collect(self::FIELD_MAP)->keys()->first(fn ($s) => str_ends_with($task->task_code, $s));
            $sampleField = $suffix ? self::FIELD_MAP[$suffix] : null;
            $date = $sampleField ? $sample->{$sampleField} : null;

            if (! $date) {
                continue;
            }

            $status = str_ends_with($task->task_code, 'approval') && $sample->status !== 'approved'
                ? $task->status // don't mark "approval" tasks done until the sample is actually approved
                : 'done';

            $task->update(['actual_date' => $date, 'status' => $status]);
            $task->plan?->recomputeCompletion();
            $synced++;
        }

        return $synced;
    }
}
