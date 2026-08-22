<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\PoProductionProgress;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\TnaTask;

class DashboardService
{
    public function merchandiser(int $userId): array
    {
        $pos = SalesContractPo::query()->with(['style', 'salesContract'])
            ->whereHas('salesContract', fn ($q) => $q->where('merchandiser_id', $userId))
            ->get();

        $poIds = $pos->pluck('id');

        return [
            'orders_by_status' => $pos->groupBy('status')->map->count(),
            'tna_due_today' => TnaTask::query()->whereHas('plan.salesContractPo', fn ($q) => $q->whereIn('id', $poIds))
                ->where('status', 'pending')->whereDate('plan_date', now()->toDateString())->count(),
            'tna_due_this_week' => TnaTask::query()->whereHas('plan.salesContractPo', fn ($q) => $q->whereIn('id', $poIds))
                ->where('status', 'pending')->whereBetween('plan_date', [now(), now()->addWeek()])->count(),
            'tna_overdue' => TnaTask::query()->whereHas('plan.salesContractPo', fn ($q) => $q->whereIn('id', $poIds))
                ->where('status', 'pending')->whereDate('plan_date', '<', now()->toDateString())->count(),
            'pcd_risk' => $pos->filter(fn ($po) => $po->tnaPlan?->pcd_result === 'fail')->count(),
            'samples_pending_approval' => Sample::query()->whereIn('style_id', $pos->pluck('style_id'))
                ->whereIn('status', ['submitted', 'in_progress'])->count(),
            'materials_not_booked' => $pos->pluck('style_id')->unique()
                ->diff(MaterialBooking::query()->whereIn('style_id', $pos->pluck('style_id'))->pluck('style_id'))->count(),
            'production_progress' => PoProductionProgress::query()->whereIn('sales_contract_po_id', $poIds)->get(),
            'shipments_this_month' => $pos->filter(fn ($po) => $po->effectiveShipment()?->isCurrentMonth())->count(),
        ];
    }

    public function management(): array
    {
        $pos = SalesContractPo::query()->with(['salesContract.buyer', 'salesContract.season', 'tnaPlan'])->get();

        $withPcd = $pos->filter(fn ($po) => $po->tnaPlan);
        $onTimePcd = $withPcd->filter(fn ($po) => $po->tnaPlan->pcd_result === 'pass');

        $shipmentPlan = app(ShipmentPlanService::class);
        $shipped = $pos->filter(fn ($po) => $po->production_plan_line_id);
        $onTimeShipped = $shipped->filter(function (SalesContractPo $po) use ($shipmentPlan) {
            $p = $shipmentPlan->forPo($po);

            return $p['actual_ship_date'] && $p['planned_ship_date'] && $p['actual_ship_date'] <= $p['planned_ship_date'];
        });

        $samples = Sample::query()->whereNotNull('approval_date')->whereNotNull('request_date')->get();
        $avgTurnaround = $samples->isNotEmpty()
            ? round($samples->avg(fn ($s) => $s->request_date->diffInDays($s->approval_date)), 1)
            : 0;

        $delayTasks = TnaTask::query()->whereNotNull('actual_date')->get()->filter(fn ($t) => ($t->daysLate() ?? 0) > 0);

        return [
            'order_book_value_by_buyer' => $pos->groupBy(fn ($po) => $po->salesContract->buyer->name ?? 'Unknown')
                ->map(fn ($group) => $group->sum(fn ($po) => $po->effectiveQty() * (float) $po->unit_price)),
            'order_book_value_by_season' => $pos->groupBy(fn ($po) => $po->salesContract->season->name ?? 'Unknown')
                ->map(fn ($group) => $group->sum(fn ($po) => $po->effectiveQty() * (float) $po->unit_price)),
            'on_time_pcd_percent' => $withPcd->count() > 0 ? round($onTimePcd->count() / $withPcd->count() * 100, 1) : 0,
            'on_time_shipment_percent' => $shipped->count() > 0 ? round($onTimeShipped->count() / $shipped->count() * 100, 1) : 0,
            'avg_sample_turnaround_days' => $avgTurnaround,
            'delay_reasons_pareto' => $delayTasks->groupBy('task_name')->map->count()->sortDesc()->take(10),
            'pcd_failures_by_dept' => $pos->filter(fn ($po) => $po->tnaPlan?->pcd_result === 'fail')
                ->groupBy(fn ($po) => $po->tnaPlan->responsibleDept?->name ?? 'Unassigned')->map->count(),
        ];
    }
}
