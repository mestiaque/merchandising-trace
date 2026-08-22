<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\TnaPlan;

/**
 * §8.3 "Export to Excel in exactly the uploaded layout; import to bulk-
 * update actual dates" / test #15. One row per PO, one column per distinct
 * task_name found across the matched plans, so a re-import can walk the
 * same header row back into the same tasks by name. Column "PO No." is the
 * row key TnaGridImportService matches on.
 */
class TnaGridExportService
{
    public function export(array $filters = []): array
    {
        $plans = TnaPlan::query()
            ->with(['salesContractPo.salesContract.buyer', 'salesContractPo.style', 'tasks'])
            ->when($filters['buyer_id'] ?? null, fn ($q, $v) => $q->whereHas('salesContractPo.salesContract', fn ($q2) => $q2->where('buyer_id', $v)))
            ->get();

        $taskNames = $plans->flatMap(fn (TnaPlan $p) => $p->tasks->pluck('task_name'))->unique()->values();

        $rows = $plans->map(function (TnaPlan $plan) use ($taskNames) {
            $po = $plan->salesContractPo;
            $row = [
                'PO No.' => $po->po_no,
                'Style' => $po->style->style_no ?? '-',
                'Buyer' => $po->salesContract->buyer->name ?? '-',
                'PCD Result' => $plan->pcd_result,
            ];

            $tasksByName = $plan->tasks->keyBy('task_name');
            foreach ($taskNames as $name) {
                $task = $tasksByName->get($name);
                if (! $task) {
                    $row[$name] = '';
                    continue;
                }

                $row[$name] = match ($task->value_type) {
                    'date' => optional($task->actual_date)->format('Y-m-d'),
                    'number' => $task->value_number,
                    default => $task->value_text,
                };
            }

            return $row;
        });

        $headers = array_merge(['PO No.', 'Style', 'Buyer', 'PCD Result'], $taskNames->all());

        return ['headers' => $headers, 'rows' => $rows->all()];
    }
}
