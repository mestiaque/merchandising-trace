<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Bridge\TrcStylePart;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContractPo;

/**
 * §M11 pre-flight checklist — every check is derived from data that already
 * lives in another module (never a field typed just for this screen).
 */
class PreFlightChecklistService
{
    public function evaluate(SalesContractPo $po): array
    {
        $checks = [
            'order_confirmed' => [
                'label' => 'Order confirmed',
                'pass' => $po->salesContract?->status === 'confirmed',
            ],
            'cost_sheet_approved' => [
                'label' => 'Cost sheet approved',
                'pass' => CostSheet::query()->where('style_id', $po->style_id)->where('status', 'approved')->exists(),
            ],
            'bom_approved' => [
                'label' => 'BOM approved',
                'pass' => Bom::query()->where('style_id', $po->style_id)->where('status', 'approved')->exists(),
            ],
            'pp_sample_approved' => [
                'label' => 'PP sample approved',
                'pass' => Sample::query()
                    ->where('style_id', $po->style_id)
                    ->where('status', 'approved')
                    ->whereHas('sampleType', fn ($q) => $q->where('code', 'PP1'))
                    ->exists(),
            ],
            'pcd_pass' => [
                'label' => 'PCD result = pass',
                'pass' => $po->tnaPlan?->pcd_result === 'pass',
            ],
            'fabric_in_house' => [
                'label' => 'Fabric in-house (1st consignment)',
                'pass' => $this->fabricInHouse($po),
            ],
            'sewing_trims_in_house' => [
                'label' => 'Sewing trims in-house (all mandatory items)',
                'pass' => $this->mandatoryTrimsDone($po),
            ],
            'size_breakdown_complete' => [
                'label' => 'Size breakdown complete',
                'pass' => $po->sizeQtyMatchesEffectiveQty(),
            ],
            'style_parts_defined' => [
                'label' => 'Style parts defined',
                'pass' => TrcStylePart::query()->where('style_id', $po->style_id)->exists(),
            ],
        ];

        $checks['all_passed'] = array_reduce($checks, fn ($carry, $c) => $carry && ($c['pass'] ?? true), true);

        return $checks;
    }

    private function fabricInHouse(SalesContractPo $po): bool
    {
        return MaterialBooking::query()
            ->where('style_id', $po->style_id)
            ->where('type', 'fabric')
            ->get()
            ->contains(fn (MaterialBooking $b) => $b->balanceQty() <= 0 && $b->totalBookedQty() > 0);
    }

    private function mandatoryTrimsDone(SalesContractPo $po): bool
    {
        $plan = $po->tnaPlan;
        if (! $plan) {
            return false;
        }

        return $plan->tasks()
            ->where('group_name', 'Sewing trims Status')
            ->where('is_mandatory', true)
            ->get()
            ->every(fn ($t) => in_array($t->status, ['done', 'approved', 'na'], true));
    }
}
