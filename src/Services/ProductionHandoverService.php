<?php

namespace ME\MerchandisingTrace\Services;

use Illuminate\Support\Facades\DB;
use ME\MerchandisingTrace\Models\Bridge\TrcPlanLine;
use ME\MerchandisingTrace\Models\Bridge\TrcPlanLineSize;
use ME\MerchandisingTrace\Models\Bridge\TrcPlanStyle;
use ME\MerchandisingTrace\Models\Bridge\TrcProductionPlan;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\ProductionHandover;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContractPo;

/**
 * The merch-to-production bridge. Writes into the OTHER package's own
 * tables (trc_production_plans / trc_plan_styles / trc_plan_lines /
 * trc_plan_line_sizes) via the soft-referenced Bridge models, without
 * touching a single file inside that other package.
 */
class ProductionHandoverService
{
    public function __construct(
        private readonly PreFlightChecklistService $checklist,
    ) {
    }

    public function push(SalesContractPo $po, int $userId, ?string $overrideReason = null): ProductionHandover
    {
        if ($po->production_plan_line_id) {
            throw new \RuntimeException('This PO has already been handed over.');
        }

        $style = $po->style;
        if (! $style->trc_product_id || ! $style->trc_size_group_id) {
            throw new \RuntimeException('This style has no Production Product / Size Group mapping yet — set it on the style before handover.');
        }

        $checks = $this->checklist->evaluate($po);
        $blockingFailed = collect($checks)->except('all_passed')->reject(fn ($c) => $c['pass']);

        if ($blockingFailed->isNotEmpty() && ! $overrideReason) {
            throw new \RuntimeException('Pre-flight checklist failed: ' . $blockingFailed->pluck('label')->implode(', '));
        }

        return DB::transaction(fn () => $this->createHandover($po, $style, $userId, $overrideReason, $checks));
    }

    private function createHandover(SalesContractPo $po, $style, int $userId, ?string $overrideReason, array $checks): ProductionHandover
    {
        $plan = TrcProductionPlan::firstOrCreate(
            ['buyer_id' => $po->salesContract->buyer_id, 'product_id' => $style->trc_product_id, 'up_date' => now()->toDateString(), 'status' => 'draft'],
            ['plan_no' => $this->nextPlanNo(), 'season_id' => null, 'size_group_id' => $style->trc_size_group_id, 'created_by' => $userId]
        );

        $planStyle = TrcPlanStyle::firstOrCreate(
            ['production_plan_id' => $plan->id, 'style_id' => $style->id],
            ['style_name' => $style->name, 'style_no' => $style->style_no, 'styling_confirmed' => 'yes', 'sort_order' => 0]
        );

        $ppSubmit = $po->tnaPlan?->tasks()->where('task_code', 'pp1_submit')->first();
        $ppMeeting = $po->tnaPlan?->tasks()->where('task_code', 'pp_meeting')->first();
        $fileHandover = $po->tnaPlan?->tasks()->where('task_code', 'file_handover')->first();
        $pullout = $po->tnaPlan?->tasks()->where('task_code', 'pullout')->first();

        $ppSampleApproved = Sample::query()->where('style_id', $style->id)->where('status', 'approved')
            ->whereHas('sampleType', fn ($q) => $q->where('code', 'PP1'))->exists();
        $trimsInHouse = $checks['sewing_trims_in_house']['pass'];

        $mainFabric = Bom::query()->where('style_id', $style->id)->where('status', 'approved')
            ->latest('version')->first()?->items()->orderBy('id')->first();

        $line = TrcPlanLine::create([
            'plan_style_id' => $planStyle->id,
            'sl_no' => TrcPlanLine::where('plan_style_id', $planStyle->id)->count() + 1,
            'po_number' => $po->po_no,
            'merch_order_id' => $po->id,
            'color_id' => $po->color_id,
            'fabric_id' => null,
            'fabric_code' => $mainFabric->item->code ?? null,
            'sample_rcv_status' => $ppSampleApproved ? 'yes' : 'no',
            'trim_card_rcv_status' => $trimsInHouse ? 'yes' : 'no',
            'total_order_qty' => $po->effectiveQty(),
            'sample_send_date' => $ppSubmit?->effectiveDate(),
            'pp_meeting_date' => $ppMeeting?->effectiveDate(),
            'plan_cut_date' => $po->effectivePcd(),
            'plan_cut_close_date' => $pullout?->effectiveDate(),
            'sewing_start_date' => $fileHandover?->effectiveDate(),
            'shipment_date' => $po->effectiveShipment(),
            'status' => 'pending',
        ]);

        foreach ($po->sizes as $size) {
            TrcPlanLineSize::create([
                'plan_line_id' => $line->id,
                'size_id' => $size->size_id,
                'order_qty' => $size->qty,
            ]);
        }

        $po->update(['production_plan_line_id' => $line->id, 'status' => 'in_production']);

        $pcdStatus = $po->tnaPlan?->pcd_result === 'pass' ? 'pass' : ($overrideReason ? 'overridden' : 'fail');

        return ProductionHandover::create([
            'sales_contract_po_id' => $po->id,
            'plan_line_id' => $line->id,
            'handover_date' => now(),
            'handed_over_by' => $userId,
            'pcd_status' => $pcdStatus,
            'override_reason' => $overrideReason,
            'checklist_snapshot' => $checks,
            'status' => 'handed_over',
        ]);
    }

    public function rollback(SalesContractPo $po, string $reason): void
    {
        $handover = ProductionHandover::where('sales_contract_po_id', $po->id)->where('status', 'handed_over')->latest('id')->first();
        if (! $handover) {
            throw new \RuntimeException('No active handover found for this PO.');
        }

        $line = TrcPlanLine::find($handover->plan_line_id);
        if ($line && $line->status !== 'pending') {
            throw new \RuntimeException('Rollback is only allowed while the plan line is still pending.');
        }

        DB::transaction(function () use ($po, $handover, $line, $reason) {
            $line?->sizes()->delete();
            $line?->delete();
            $po->update(['production_plan_line_id' => null, 'status' => 'pcd_passed']);
            $handover->update(['status' => 'rolled_back', 'rollback_reason' => $reason]);
        });
    }

    private function nextPlanNo(): string
    {
        $year = now()->format('y');
        $prefix = "PP-{$year}-";
        $last = TrcProductionPlan::withTrashed()
            ->where('plan_no', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->max(DB::raw("CAST(SUBSTRING_INDEX(plan_no, '-', -1) AS UNSIGNED)"));

        return $prefix . str_pad((string) ((int) $last + 1), 5, '0', STR_PAD_LEFT);
    }
}
