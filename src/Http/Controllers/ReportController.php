<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use ME\MerchandisingTrace\Models\Costing;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\ShipmentPlan;
use ME\MerchandisingTrace\Models\TnaMilestone;

class ReportController extends Controller
{
    private const REPORTS = [
        ['key' => 'order-summary', 'label' => 'Order Summary', 'icon' => 'fa-file-invoice'],
        ['key' => 'costing-report', 'label' => 'Costing Report', 'icon' => 'fa-calculator'],
        ['key' => 'sample-status', 'label' => 'Sample Status', 'icon' => 'fa-vial'],
        ['key' => 'tna-report', 'label' => 'TNA Report', 'icon' => 'fa-calendar-check'],
        ['key' => 'shipment-report', 'label' => 'Shipment Report', 'icon' => 'fa-ship'],
        ['key' => 'buyer-wise', 'label' => 'Buyer Wise', 'icon' => 'fa-handshake'],
        ['key' => 'style-wise', 'label' => 'Style Wise', 'icon' => 'fa-shirt'],
    ];

    public function index(): View
    {
        $this->authorize('merch_report.view');

        $reports = collect(self::REPORTS)->map(fn ($r) => $r + ['route' => 'merchandising-trace.reports.' . $r['key']]);

        return view('merchandising-trace::admin.reports.index', compact('reports'));
    }

    public function orderSummary(Request $request): View
    {
        $this->authorize('merch_report.view');

        $rows = Order::query()
            ->with(['buyer', 'style'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->limit(500)
            ->get();

        return $this->render('Order Summary', [
            'po_number' => 'PO Number', 'buyer.name' => 'Buyer', 'style.name' => 'Style', 'order_qty' => 'Order Qty',
            'delivery_date' => 'Delivery Date', 'status' => 'Status',
        ], $rows, [
            ['name' => 'status', 'label' => 'All Status', 'type' => 'select', 'options' => collect([
                (object) ['id' => 'pending', 'name' => 'Pending'], (object) ['id' => 'confirmed', 'name' => 'Confirmed'],
                (object) ['id' => 'in_production', 'name' => 'In Production'], (object) ['id' => 'completed', 'name' => 'Completed'],
                (object) ['id' => 'cancelled', 'name' => 'Cancelled'],
            ])],
        ]);
    }

    public function costingReport(Request $request): View
    {
        $this->authorize('merch_report.view');

        $rows = Costing::query()
            ->with(['order.buyer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->limit(500)
            ->get()
            ->map(fn (Costing $c) => (object) [
                'costing_number' => $c->costing_number,
                'order' => $c->order,
                'fob_price' => number_format($c->fob_price, 2),
                'total_cost' => number_format($c->totalCost(), 2),
                'cm' => number_format($c->cmAmount(), 2),
                'margin' => $c->profitMarginPercent() . '%',
                'status' => ucfirst($c->status),
            ]);

        return $this->render('Costing Report', [
            'costing_number' => 'Costing No', 'order.po_number' => 'Order', 'order.buyer.name' => 'Buyer',
            'fob_price' => 'FOB Price', 'total_cost' => 'Total Cost', 'cm' => 'CM', 'margin' => 'Margin', 'status' => 'Status',
        ], $rows, []);
    }

    public function sampleStatus(Request $request): View
    {
        $this->authorize('merch_report.view');

        $rows = Sample::query()
            ->with(['buyer', 'style'])
            ->when($request->filled('sample_type'), fn ($q) => $q->where('sample_type', $request->sample_type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->limit(500)
            ->get();

        return $this->render('Sample Status', [
            'sample_number' => 'Sample No', 'buyer.name' => 'Buyer', 'style.name' => 'Style',
            'sample_type' => 'Type', 'qty' => 'Qty', 'status' => 'Status',
        ], $rows, [
            ['name' => 'sample_type', 'label' => 'All Types', 'type' => 'select', 'options' => collect(Sample::SAMPLE_TYPES)->map(fn ($t) => (object) ['id' => $t, 'name' => ucfirst(str_replace('_', ' ', $t))])],
        ]);
    }

    public function tnaReport(Request $request): View
    {
        $this->authorize('merch_report.view');

        $rows = TnaMilestone::query()
            ->with(['order'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('planned_date')
            ->limit(500)
            ->get()
            ->map(fn (TnaMilestone $m) => (object) [
                'order' => $m->order,
                'milestone_name' => $m->milestone_name,
                'planned_date' => $m->planned_date->format('d M Y'),
                'actual_date' => optional($m->actual_date)->format('d M Y') ?? '-',
                'status' => $m->isDelayed() ? 'Delayed' : ucfirst($m->status),
            ]);

        return $this->render('TNA Report', [
            'order.po_number' => 'Order', 'milestone_name' => 'Milestone', 'planned_date' => 'Planned Date',
            'actual_date' => 'Actual Date', 'status' => 'Status',
        ], $rows, []);
    }

    public function shipmentReport(Request $request): View
    {
        $this->authorize('merch_report.view');

        $rows = ShipmentPlan::query()
            ->with(['order.buyer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->limit(500)
            ->get();

        return $this->render('Shipment Report', [
            'plan_number' => 'Plan No', 'order.po_number' => 'Order', 'order.buyer.name' => 'Buyer',
            'planned_date' => 'Planned Date', 'planned_qty' => 'Planned Qty', 'status' => 'Status',
        ], $rows, []);
    }

    public function buyerWise(Request $request): View
    {
        $this->authorize('merch_report.view');

        $rows = Order::query()
            ->selectRaw('buyer_id, COUNT(*) as order_count, SUM(order_qty) as total_qty')
            ->groupBy('buyer_id')
            ->orderByDesc('total_qty')
            ->with('buyer')
            ->get();

        return $this->render('Buyer Wise Report', [
            'buyer.name' => 'Buyer', 'order_count' => 'Orders', 'total_qty' => 'Total Qty',
        ], $rows, []);
    }

    public function styleWise(Request $request): View
    {
        $this->authorize('merch_report.view');

        $rows = Order::query()
            ->selectRaw('style_id, COUNT(*) as order_count, SUM(order_qty) as total_qty')
            ->groupBy('style_id')
            ->orderByDesc('total_qty')
            ->with('style')
            ->get();

        return $this->render('Style Wise Report', [
            'style.name' => 'Style', 'order_count' => 'Orders', 'total_qty' => 'Total Qty',
        ], $rows, []);
    }

    /**
     * Every report method calls this once at the end — request()->boolean('print')
     * switches it to the printMaster2-based print view instead of the normal
     * DataTables page, mirroring production-sfl's ReportController.
     */
    private function render(string $title, array $columns, $rows, array $filters): View
    {
        if (request()->boolean('print')) {
            return view('merchandising-trace::admin.partials.print-table', [
                'title'   => $title,
                'columns' => $columns,
                'rows'    => $rows,
            ]);
        }

        return view('merchandising-trace::admin.reports.table', [
            'title'   => $title,
            'columns' => $columns,
            'rows'    => $rows,
            'filters' => $filters,
        ]);
    }
}
