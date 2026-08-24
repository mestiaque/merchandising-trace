<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\PoProductionProgress;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\TnaPlan;
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
            'production_progress' => $productionProgress = PoProductionProgress::query()->whereIn('sales_contract_po_id', $poIds)->get(),
            'shipments_this_month' => $pos->filter(fn ($po) => $po->effectiveShipment()?->isCurrentMonth())->count(),
            'tna_trend_30d' => $this->tnaTrend30d($poIds),
            'wip_by_stage' => $this->wipByStage($productionProgress),
            'plans_by_status' => TnaPlan::query()->whereIn('sales_contract_po_id', $poIds)
                ->get()->groupBy('overall_status')->map->count(),
            'shipments_by_month' => $this->shipmentsByMonth($pos),
            'top_delay_reasons' => $this->topDelayReasons($poIds),
            'top_buyers_by_qty' => $pos->groupBy(fn ($po) => $po->salesContract->buyer->name ?? 'Unknown')
                ->map(fn ($group) => $group->sum(fn ($po) => $po->effectiveQty()))
                ->sortDesc()->take(8),
            'top_styles_by_qty' => $pos->groupBy('style_id')
                ->map(fn ($group) => [
                    'name' => $group->first()->style->name ?? '-',
                    'style_no' => $group->first()->style->style_no ?? '-',
                    'qty' => $group->sum(fn ($po) => $po->effectiveQty()),
                ])
                ->sortByDesc('qty')->take(10)->values(),
            'orders_at_risk' => $pos->filter(fn ($po) => $po->effectiveShipment()?->isPast() && ! in_array($po->status, ['shipped', 'closed']))
                ->sortBy(fn ($po) => $po->effectiveShipment())
                ->take(10)
                ->map(fn ($po) => [
                    'style_po' => ($po->style->style_no ?? '-').' / '.($po->po_no ?? '-'),
                    'shipment_date' => $po->effectiveShipment()->format('d M Y'),
                    'status' => $po->status,
                ])
                ->values(),
        ];
    }

    private function tnaTrend30d($poIds): array
    {
        $windowStart = now()->subDays(29)->startOfDay();

        $tasks = TnaTask::query()->whereHas('plan.salesContractPo', fn ($q) => $q->whereIn('id', $poIds))
            ->where(fn ($q) => $q->where('plan_date', '>=', $windowStart)->orWhere('actual_date', '>=', $windowStart))
            ->get();

        $planned = $tasks->groupBy(fn ($t) => $t->plan_date?->toDateString())->map->count();
        $completed = $tasks->filter(fn ($t) => in_array($t->status, ['done', 'approved']))
            ->groupBy(fn ($t) => $t->actual_date?->toDateString())->map->count();

        return collect(range(0, 29))->mapWithKeys(function ($i) use ($planned, $completed) {
            $date = now()->subDays(29 - $i)->toDateString();

            return [$date => ['planned' => $planned->get($date, 0), 'completed' => $completed->get($date, 0)]];
        })->toArray();
    }

    private function wipByStage($productionProgress): array
    {
        $stages = ['Not Started' => 0, 'Cutting' => 0, 'Sewing' => 0, 'Finishing' => 0, 'Packing' => 0, 'Shipped' => 0];

        return collect($stages)->merge($productionProgress->countBy(fn ($p) => $p->currentStage()))->toArray();
    }

    private function shipmentsByMonth($pos): array
    {
        $counts = $pos->filter(fn ($po) => $po->effectiveShipment())
            ->groupBy(fn ($po) => $po->effectiveShipment()->format('M Y'))->map->count();

        return collect(range(0, 5))->mapWithKeys(function ($i) use ($counts) {
            $month = now()->subMonths(5 - $i)->format('M Y');

            return [$month => $counts->get($month, 0)];
        })->toArray();
    }

    private function topDelayReasons($poIds)
    {
        return TnaTask::query()->whereHas('plan.salesContractPo', fn ($q) => $q->whereIn('id', $poIds))
            ->whereNotNull('actual_date')->get()
            ->filter(fn ($t) => ($t->daysLate() ?? 0) > 0)
            ->groupBy('task_name')->map->count()->sortDesc()->take(5);
    }

    /**
     * Aggregate (all-merchandisers) figures for the compact widget embedded
     * on the host app's own aggregate dashboard — same shape as merchandiser()
     * but without the merchandiser_id scoping.
     */
    public function overview(): array
    {
        $pos = SalesContractPo::query()->with(['style', 'salesContract.buyer', 'tnaPlan'])->get();
        $poIds = $pos->pluck('id');
        $withPcd = $pos->filter(fn ($po) => $po->tnaPlan);

        return [
            'active_pos' => $pos->whereNotIn('status', ['closed'])->count(),
            'on_time_pcd_percent' => $withPcd->count() > 0
                ? round($withPcd->filter(fn ($po) => $po->tnaPlan->pcd_result === 'pass')->count() / $withPcd->count() * 100, 1)
                : 0,
            'tna_due_today' => TnaTask::query()->whereHas('plan.salesContractPo', fn ($q) => $q->whereIn('id', $poIds))
                ->where('status', 'pending')->whereDate('plan_date', now()->toDateString())->count(),
            'pcd_risk' => $pos->filter(fn ($po) => $po->tnaPlan?->pcd_result === 'fail')->count(),
            'samples_pending_approval' => Sample::query()->whereIn('style_id', $pos->pluck('style_id'))
                ->whereIn('status', ['submitted', 'in_progress'])->count(),
            'materials_not_booked' => $pos->pluck('style_id')->unique()
                ->diff(MaterialBooking::query()->whereIn('style_id', $pos->pluck('style_id'))->pluck('style_id'))->count(),
            'shipments_this_month' => $pos->filter(fn ($po) => $po->effectiveShipment()?->isCurrentMonth())->count(),
            'tna_trend_30d' => $this->tnaTrend30d($poIds),
            'wip_by_stage' => $this->wipByStage(PoProductionProgress::query()->whereIn('sales_contract_po_id', $poIds)->get()),
            'plans_by_status' => TnaPlan::query()->whereIn('sales_contract_po_id', $poIds)
                ->get()->groupBy('overall_status')->map->count(),
            'top_buyers_by_qty' => $pos->groupBy(fn ($po) => $po->salesContract->buyer->name ?? 'Unknown')
                ->map(fn ($group) => $group->sum(fn ($po) => $po->effectiveQty()))
                ->sortDesc()->take(5),
            'top_styles_by_qty' => $pos->groupBy('style_id')
                ->map(fn ($group) => [
                    'name' => $group->first()->style->name ?? '-',
                    'style_no' => $group->first()->style->style_no ?? '-',
                    'qty' => $group->sum(fn ($po) => $po->effectiveQty()),
                ])
                ->sortByDesc('qty')->take(5)->values(),
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
