<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\CostingRequest;
use ME\MerchandisingTrace\Models\Costing;
use ME\MerchandisingTrace\Models\Order;

class CostingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_costing.list');

        $costings = Costing::query()
            ->with(['order.buyer'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.costings.index', ['costings' => $costings] + $this->formOptions());
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_costing.list');

        $costings = Costing::query()
            ->with(['order.buyer'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Costings',
            'columns' => ['costing_number' => 'Costing No', 'order.po_number' => 'Order', 'order.buyer.name' => 'Buyer', 'fob_price' => 'FOB Price', 'status' => 'Status'],
            'rows'    => $costings,
        ]);
    }

    public function store(CostingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $costing = Costing::create($data);

        return back()->with('success', "Costing {$costing->costing_number} created successfully.");
    }

    public function update(CostingRequest $request, Costing $costing): RedirectResponse
    {
        $costing->update($request->validated());

        return back()->with('success', 'Costing updated successfully.');
    }

    public function destroy(Costing $costing): RedirectResponse
    {
        $this->authorize('merch_costing.delete');

        $costing->delete();

        return back()->with('success', 'Costing deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'ordersOptions' => Order::query()->orderByDesc('id')->limit(200)->get(),
        ];
    }
}
