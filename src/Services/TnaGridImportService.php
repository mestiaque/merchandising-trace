<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\TnaTask;

/**
 * §8.3 "import to bulk-update actual dates" / test #15. Takes the raw 2D
 * array from TnaGridExportService's own layout (header row + data rows,
 * "PO No." as the row key) and writes actual_date/value_text/value_number
 * back onto the matching tasks -- but §6 Rule 3 ("auto-synced T&A cells
 * are read-only... cannot be edited via the API either") means any column
 * matching an auto-filled task (is_auto = true) is silently skipped, never
 * overwritten, no matter what the file contains.
 */
class TnaGridImportService
{
    public function import(array $sheet, int $userId): array
    {
        $header = array_shift($sheet) ?? [];
        $poCol = array_search('PO No.', $header, true);

        if ($poCol === false) {
            throw new \RuntimeException('Column "PO No." not found in the uploaded file.');
        }

        $updated = 0;
        $skippedAuto = 0;
        $poNotFound = 0;

        foreach ($sheet as $row) {
            $poNo = $row[$poCol] ?? null;
            if (! $poNo) {
                continue;
            }

            $po = SalesContractPo::where('po_no', $poNo)->first();
            $plan = $po?->tnaPlan;
            if (! $plan) {
                $poNotFound++;
                continue;
            }

            $tasksByName = $plan->tasks()->get()->keyBy('task_name');

            foreach ($header as $col => $taskName) {
                if ($col === $poCol || in_array($taskName, ['Style', 'Buyer', 'PCD Result'], true)) {
                    continue;
                }

                $task = $tasksByName->get($taskName);
                $value = $row[$col] ?? null;

                if (! $task || $value === null || $value === '') {
                    continue;
                }

                if ($task->is_auto) {
                    $skippedAuto++;
                    continue;
                }

                $this->applyValue($task, $value, $userId);
                $updated++;
            }

            $plan->recomputeCompletion();
        }

        return ['updated' => $updated, 'skipped_auto' => $skippedAuto, 'po_not_found' => $poNotFound];
    }

    private function applyValue(TnaTask $task, $value, int $userId): void
    {
        $field = $task->value_type === 'date' ? 'actual_date' : ($task->value_type === 'number' ? 'value_number' : 'value_text');
        $newValue = $task->value_type === 'date' ? \Illuminate\Support\Carbon::parse($value)->toDateString() : $value;

        if ((string) $task->{$field} === (string) $newValue) {
            return;
        }

        $task->logs()->create([
            'field' => $field,
            'old_value' => $task->{$field},
            'new_value' => $newValue,
            'changed_by' => $userId,
            'changed_at' => now(),
            'reason' => 'Excel import',
        ]);

        $task->update([$field => $newValue, 'status' => $task->value_type === 'date' ? 'done' : $task->status]);
    }
}
