<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\ShipmentPlanRequest;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\ShipmentPlan;

class ShipmentPlanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_shipment_plan.list');

        $shipment_plans = ShipmentPlan::query()
            ->with(['order.buyer'])
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->order_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.shipment-plans.index', ['shipment_plans' => $shipment_plans] + $this->formOptions());
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_shipment_plan.list');

        $shipment_plans = ShipmentPlan::query()
            ->with(['order.buyer'])
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->order_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Shipment Plans',
            'columns' => ['plan_number' => 'Plan No', 'order.po_number' => 'Order', 'order.buyer.name' => 'Buyer', 'planned_date' => 'Planned Date', 'planned_qty' => 'Planned Qty', 'status' => 'Status'],
            'rows'    => $shipment_plans,
        ]);
    }

    public function store(ShipmentPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $plan = ShipmentPlan::create($data);

        return back()->with('success', "Shipment plan {$plan->plan_number} created successfully.");
    }

    public function update(ShipmentPlanRequest $request, ShipmentPlan $shipment_plan): RedirectResponse
    {
        $shipment_plan->update($request->validated());

        return back()->with('success', 'Shipment plan updated successfully.');
    }

    public function destroy(ShipmentPlan $shipment_plan): RedirectResponse
    {
        $this->authorize('merch_shipment_plan.delete');

        $shipment_plan->delete();

        return back()->with('success', 'Shipment plan deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'ordersOptions' => Order::query()->orderByDesc('id')->limit(200)->get(),
        ];
    }
}
