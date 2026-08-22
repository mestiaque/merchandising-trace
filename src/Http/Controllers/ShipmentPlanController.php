<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Services\ShipmentPlanService;

class ShipmentPlanController extends Controller
{
    public function index(Request $request, ShipmentPlanService $shipmentPlan): View
    {
        $this->authorize('merch_shipment_plan.list');

        $pos = SalesContractPo::query()
            ->with(['style', 'salesContract.buyer'])
            ->whereNotNull('shipment_date')
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('shipment_date', $request->month))
            ->orderBy('shipment_date')
            ->paginate(30)
            ->withQueryString();

        $rows = $pos->getCollection()->map(fn (SalesContractPo $po) => [
            'po' => $po,
            'plan' => $shipmentPlan->forPo($po),
        ]);

        return view('merchandising-trace::admin.shipment-plans.index', [
            'rows' => $rows,
            'paginator' => $pos,
        ]);
    }

    public function show(SalesContractPo $sales_contract_po, ShipmentPlanService $shipmentPlan): View
    {
        $this->authorize('merch_shipment_plan.view');

        $sales_contract_po->load(['style', 'salesContract.buyer', 'shipmentBookings']);

        return view('merchandising-trace::admin.shipment-plans.show', [
            'po' => $sales_contract_po,
            'plan' => $shipmentPlan->forPo($sales_contract_po),
        ]);
    }

    public function storeBooking(Request $request, SalesContractPo $sales_contract_po): RedirectResponse
    {
        $this->authorize('merch_shipment_plan.edit');

        $data = $request->validate([
            'planned_ship_date' => ['nullable', 'date'],
            'forwarder_name' => ['nullable', 'string', 'max:150'],
            'booking_no' => ['nullable', 'string', 'max:150'],
            'vessel_flight' => ['nullable', 'string', 'max:150'],
            'is_short' => ['nullable', 'boolean'],
            'short_reason' => ['nullable', 'required_if:is_short,1', 'string', 'max:500'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data['created_by'] = auth()->id();
        $sales_contract_po->shipmentBookings()->create($data);

        return back()->with('success', 'Shipment booking saved.');
    }
}
