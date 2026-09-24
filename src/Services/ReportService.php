<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\OrderDocument;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\TnaSubPlan;
use ME\MerchandisingTrace\Models\TnaTask;

/**
 * §M15 — all 14 reports. Each method returns ['headers' => [...], 'rows' =>
 * [[...], ...]] so ReportController can render/export any of them through
 * one generic path. Filters are the common set the spec asks for
 * (date/buyer/merchandiser/style/PO); a report ignores filters that don't
 * apply to its own data shape.
 */
class ReportService
{
    public const REPORTS = [
        'tna_status' => 'T&A Status Report',
        'pcd_analysis' => 'PCD Pass/Fail Analysis',
        'sample_turnaround' => 'Sample Status & Approval Turnaround',
        'fabric_trims_in_house' => 'Fabric & Trims In-House Status',
        'costing_summary' => 'Costing Summary & Margin Analysis',
        'order_book' => 'Order Book & Value',
        'delay_analysis' => 'Delay Analysis (Plan vs Actual)',
        'sub_tna' => 'Sub-T&A Daily Send/Receive & Balance',
        'shipment_plan_vs_actual' => 'Shipment Plan vs Actual',
        'buyer_scorecard' => 'Buyer-wise Performance Scorecard',
        'merchandiser_workload' => 'Merchandiser Workload & Performance',
        'order_profitability' => 'Order Profitability',
        'document_compliance' => 'Document Compliance Register',
    ];

    public function run(string $key, array $filters = []): array
    {
        return match ($key) {
            'tna_status' => $this->tnaStatus($filters),
            'pcd_analysis' => $this->pcdAnalysis($filters),
            'sample_turnaround' => $this->sampleTurnaround($filters),
            'fabric_trims_in_house' => $this->fabricTrimsInHouse($filters),
            'costing_summary' => $this->costingSummary($filters),
            'order_book' => $this->orderBook($filters),
            'delay_analysis' => $this->delayAnalysis($filters),
            'sub_tna' => $this->subTna($filters),
            'shipment_plan_vs_actual' => $this->shipmentPlanVsActual($filters),
            'buyer_scorecard' => $this->buyerScorecard($filters),
            'merchandiser_workload' => $this->merchandiserWorkload($filters),
            'order_profitability' => $this->orderProfitability($filters),
            'document_compliance' => $this->documentCompliance($filters),
            default => ['headers' => [], 'rows' => []],
        };
    }

    private function poQuery(array $filters)
    {
        return SalesContractPo::query()
            ->with(['style', 'salesContract.buyer', 'salesContract.merchandiser', 'tnaPlan'])
            ->when($filters['buyer_id'] ?? null, fn ($q, $v) => $q->whereHas('salesContract', fn ($q2) => $q2->where('buyer_id', $v)))
            ->when($filters['merchandiser_id'] ?? null, fn ($q, $v) => $q->whereHas('salesContract', fn ($q2) => $q2->where('merchandiser_id', $v)))
            ->when($filters['style_id'] ?? null, fn ($q, $v) => $q->where('style_id', $v))
            ->when($filters['po_no'] ?? null, fn ($q, $v) => $q->where('po_no', 'like', "%{$v}%"))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('pcd_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('pcd_date', '<=', $v));
    }

    private function tnaStatus(array $filters): array
    {
        $rows = [];
        foreach ($this->poQuery($filters)->get() as $po) {
            $plan = $po->tnaPlan;
            $row = [
                'PO No' => $po->po_no,
                'Style' => $po->style->style_no ?? '-',
                'Buyer' => $po->salesContract->buyer->name ?? '-',
                'Effective Qty' => $po->effectiveQty(),
                'Effective PCD' => optional($po->effectivePcd())->format('Y-m-d'),
                'PCD Result' => $plan->pcd_result ?? '-',
            ];
            foreach ($plan?->tasks ?? [] as $task) {
                $row["{$task->group_name}: {$task->task_name}"] = $task->value_type === 'date'
                    ? optional($task->actual_date ?? $task->effectiveDate())->format('Y-m-d')
                    : ($task->value_text ?? $task->value_number ?? $task->status);
            }
            $rows[] = $row;
        }

        $headers = $rows ? array_keys($rows[0]) : ['PO No', 'Style', 'Buyer', 'Effective Qty', 'Effective PCD', 'PCD Result'];

        return ['headers' => $headers, 'rows' => $rows];
    }

    private function pcdAnalysis(array $filters): array
    {
        $rows = $this->poQuery($filters)->get()->map(fn (SalesContractPo $po) => [
            'PO No' => $po->po_no,
            'Style' => $po->style->style_no ?? '-',
            'Buyer' => $po->salesContract->buyer->name ?? '-',
            'PCD Result' => $po->tnaPlan?->pcd_result ?? '-',
            'Fail Reason' => \ME\MerchandisingTrace\Support\RichText::plain($po->tnaPlan?->pcd_fail_reason) ?: '-',
            'Responsible Dept' => $po->tnaPlan?->responsibleDept?->name ?? '-',
            'Responsible Person' => $po->tnaPlan?->responsiblePerson?->name ?? '-',
        ])->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function sampleTurnaround(array $filters): array
    {
        $rows = Sample::query()
            ->with(['style', 'buyer', 'sampleType'])
            ->when($filters['buyer_id'] ?? null, fn ($q, $v) => $q->where('buyer_id', $v))
            ->when($filters['style_id'] ?? null, fn ($q, $v) => $q->where('style_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('request_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('request_date', '<=', $v))
            ->get()
            ->map(fn (Sample $s) => [
                'Sample No' => $s->sample_no,
                'Style' => $s->style->style_no ?? '-',
                'Buyer' => $s->buyer->name ?? '-',
                'Type' => $s->sampleType->name ?? '-',
                'Status' => $s->status,
                'Request Date' => optional($s->request_date)->format('Y-m-d'),
                'Approval Date' => optional($s->approval_date)->format('Y-m-d'),
                'Turnaround (days)' => $s->request_date && $s->approval_date ? $s->request_date->diffInDays($s->approval_date) : null,
            ])->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function fabricTrimsInHouse(array $filters): array
    {
        $checklist = app(PreFlightChecklistService::class);
        $rows = $this->poQuery($filters)->get()->map(function (SalesContractPo $po) use ($checklist) {
            $checks = $checklist->evaluate($po);

            return [
                'PO No' => $po->po_no,
                'Style' => $po->style->style_no ?? '-',
                'Fabric In-House' => $checks['fabric_in_house']['pass'] ? 'Yes' : 'No',
                'Sewing Trims In-House' => $checks['sewing_trims_in_house']['pass'] ? 'Yes' : 'No',
            ];
        })->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function costingSummary(array $filters): array
    {
        $rows = CostSheet::query()->with(['style', 'buyer'])
            ->when($filters['buyer_id'] ?? null, fn ($q, $v) => $q->where('buyer_id', $v))
            ->when($filters['style_id'] ?? null, fn ($q, $v) => $q->where('style_id', $v))
            ->get()
            ->map(fn (CostSheet $c) => [
                'Cost Sheet No' => $c->cost_sheet_no,
                'Style' => $c->styleLabel(),
                'Buyer' => $c->buyer->name ?? '-',
                'FOB / Pc' => (float) $c->total_cost,
                'FOB / Dz' => (float) $c->total_cost * 12,
                'Margin %' => $c->calcMarginPercent(),
                'Status' => $c->status,
            ])->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function orderBook(array $filters): array
    {
        $rows = $this->poQuery($filters)->get()->map(fn (SalesContractPo $po) => [
            'PO No' => $po->po_no,
            'Buyer' => $po->salesContract->buyer->name ?? '-',
            'Style' => $po->style->style_no ?? '-',
            'Effective Qty' => $po->effectiveQty(),
            'Unit Price' => (float) $po->unit_price,
            'Total Value' => $po->effectiveQty() * (float) $po->unit_price,
            'Status' => $po->status,
        ])->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function delayAnalysis(array $filters): array
    {
        $rows = TnaTask::query()->with(['plan.salesContractPo'])
            ->whereNotNull('actual_date')
            ->when($filters['po_no'] ?? null, fn ($q, $v) => $q->whereHas('plan.salesContractPo', fn ($q2) => $q2->where('po_no', 'like', "%{$v}%")))
            ->get()
            ->filter(fn (TnaTask $t) => $t->plan?->salesContractPo)
            ->map(fn (TnaTask $t) => [
                'PO No' => $t->plan->salesContractPo->po_no,
                'Milestone' => $t->task_name,
                'Plan Date' => optional($t->effectiveDate())->format('Y-m-d'),
                'Actual Date' => optional($t->actual_date)->format('Y-m-d'),
                'Days Late' => $t->daysLate(),
            ])->values()->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function subTna(array $filters): array
    {
        $rows = TnaSubPlan::query()->with('salesContractPo')
            ->when($filters['po_no'] ?? null, fn ($q, $v) => $q->whereHas('salesContractPo', fn ($q2) => $q2->where('po_no', 'like', "%{$v}%")))
            ->get()
            ->map(fn (TnaSubPlan $p) => [
                'Sub No' => $p->sub_no,
                'PO No' => $p->salesContractPo->po_no ?? '-',
                'Process' => $p->process_type,
                'PO Qty' => $p->po_qty,
                'Total Sent' => $p->totalSent(),
                'Total Received' => $p->totalReceived(),
                'Balance' => $p->balanceQty(),
            ])->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function shipmentPlanVsActual(array $filters): array
    {
        $shipmentPlan = app(ShipmentPlanService::class);
        $rows = $this->poQuery($filters)->whereNotNull('shipment_date')->get()->map(function (SalesContractPo $po) use ($shipmentPlan) {
            $p = $shipmentPlan->forPo($po);

            return [
                'PO No' => $po->po_no,
                'Buyer' => $po->salesContract->buyer->name ?? '-',
                'Planned Ship Date' => optional($p['planned_ship_date'])->format('Y-m-d'),
                'Planned Qty' => $p['planned_qty'],
                'Actual Qty' => $p['actual_qty'],
                'Short Qty' => $p['short_qty'],
                'Short %' => $p['short_percent'],
            ];
        })->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function buyerScorecard(array $filters): array
    {
        $rows = [];
        $buyers = \ME\MerchandisingTrace\Models\Buyer::query()->active()->get();

        foreach ($buyers as $buyer) {
            $pos = SalesContractPo::query()->with('tnaPlan')->whereHas('salesContract', fn ($q) => $q->where('buyer_id', $buyer->id))->get();
            if ($pos->isEmpty()) {
                continue;
            }

            $withPcd = $pos->filter(fn ($po) => $po->tnaPlan);
            $onTimePcd = $withPcd->filter(fn ($po) => $po->tnaPlan->pcd_result === 'pass');

            $rows[] = [
                'Buyer' => $buyer->name,
                'Order Count' => $pos->count(),
                'Order Value' => $pos->sum(fn ($po) => $po->effectiveQty() * (float) $po->unit_price),
                'On-time PCD %' => $withPcd->count() > 0 ? round($onTimePcd->count() / $withPcd->count() * 100, 1) : 0,
            ];
        }

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function merchandiserWorkload(array $filters): array
    {
        $rows = [];
        $merchandiserIds = \ME\MerchandisingTrace\Models\SalesContract::query()
            ->whereNotNull('merchandiser_id')->distinct()->pluck('merchandiser_id');
        $users = \App\Models\User::query()->whereIn('id', $merchandiserIds)->get()->keyBy('id');

        foreach ($merchandiserIds as $userId) {
            $pos = SalesContractPo::query()->whereHas('salesContract', fn ($q) => $q->where('merchandiser_id', $userId))->get();

            $rows[] = [
                'Merchandiser' => $users->get($userId)?->name ?? "User #{$userId}",
                'Order Count' => $pos->count(),
                'Order Value' => $pos->sum(fn ($po) => $po->effectiveQty() * (float) $po->unit_price),
            ];
        }

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    /**
     * "Actual cost" tracking is out of scope for this build (no costing-
     * actuals capture module exists anywhere in the spec) — this report is
     * honest about that gap rather than fabricating a number: it reports
     * the planned cost sheet only.
     */
    private function orderProfitability(array $filters): array
    {
        $rows = CostSheet::query()->with(['style', 'buyer'])
            ->where('status', 'approved')
            ->when($filters['buyer_id'] ?? null, fn ($q, $v) => $q->where('buyer_id', $v))
            ->get()
            ->map(fn (CostSheet $c) => [
                'Style' => $c->styleLabel(),
                'Buyer' => $c->buyer->name ?? '-',
                'Planned FOB / Pc' => (float) $c->total_cost,
                'Final Price' => (float) ($c->final_price ?: $c->offer_price),
                'Planned Margin %' => $c->calcMarginPercent(),
                'Actual Cost' => 'not tracked',
            ])->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }

    private function documentCompliance(array $filters): array
    {
        $rows = OrderDocument::query()->with('salesContract.buyer')
            ->when($filters['buyer_id'] ?? null, fn ($q, $v) => $q->whereHas('salesContract', fn ($q2) => $q2->where('buyer_id', $v)))
            ->get()
            ->map(fn (OrderDocument $d) => [
                'Contract No' => $d->salesContract->contract_no ?? '-',
                'Buyer' => $d->salesContract->buyer->name ?? '-',
                'Document' => $d->name,
                'Mandatory' => $d->is_mandatory ? 'Yes' : 'No',
                'Due Date' => optional($d->due_date)->format('Y-m-d'),
                'Status' => $d->status,
                'Overdue' => $d->isOverdue() ? 'Yes' : 'No',
            ])->all();

        return ['headers' => $rows ? array_keys($rows[0]) : [], 'rows' => $rows];
    }
}
