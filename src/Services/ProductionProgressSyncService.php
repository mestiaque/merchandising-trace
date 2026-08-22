<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\Bridge\TrcPlanLine;
use ME\MerchandisingTrace\Models\PoProductionProgress;
use ME\MerchandisingTrace\Models\SalesContractPo;

/**
 * Reverse progress feed — mirrors the OTHER package's plan-line-size
 * rollups into a merchandising-owned read-model so a merchandiser dashboard
 * never queries that package's tables directly.
 */
class ProductionProgressSyncService
{
    public function syncAll(): int
    {
        $synced = 0;

        SalesContractPo::query()
            ->whereNotNull('production_plan_line_id')
            ->each(function (SalesContractPo $po) use (&$synced) {
                $this->syncFor($po);
                $synced++;
            });

        return $synced;
    }

    public function syncFor(SalesContractPo $po): ?PoProductionProgress
    {
        $line = TrcPlanLine::with('sizes')->find($po->production_plan_line_id);
        if (! $line) {
            return null;
        }

        $sizes = $line->sizes;
        $cut = (int) $sizes->sum('cut_qty');
        $sewn = (int) $sizes->sum('sewn_qty');
        $finished = (int) $sizes->sum('finished_qty');
        $packed = (int) $sizes->sum('packed_qty');
        $shipped = (int) $sizes->sum('shipped_qty');
        $reject = (int) $sizes->sum('reject_qty');
        $inspected = (int) $sizes->sum('passed_qty') + $reject;
        $dhu = $inspected > 0 ? round($reject / $inspected * 100, 2) : 0;

        return PoProductionProgress::updateOrCreate(
            ['sales_contract_po_id' => $po->id],
            [
                'order_qty' => $po->effectiveQty(),
                'cut_qty' => $cut,
                'sewn_qty' => $sewn,
                'finished_qty' => $finished,
                'packed_qty' => $packed,
                'shipped_qty' => $shipped,
                'reject_qty' => $reject,
                'dhu' => $dhu,
                'synced_at' => now(),
            ]
        );
    }
}
