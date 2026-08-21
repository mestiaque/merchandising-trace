<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SalesContractRequest;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\SalesContract;

class SalesContractController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_sales_contract.list');

        $sales_contracts = SalesContract::query()
            ->with(['order', 'buyer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.sales-contracts.index', ['sales_contracts' => $sales_contracts] + $this->formOptions());
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_sales_contract.list');

        $sales_contracts = SalesContract::query()
            ->with(['order', 'buyer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Sales Contracts',
            'columns' => ['contract_number' => 'Contract No', 'order.po_number' => 'Order', 'buyer.name' => 'Buyer', 'contract_date' => 'Contract Date', 'status' => 'Status'],
            'rows'    => $sales_contracts,
        ]);
    }

    public function store(SalesContractRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['buyer_id'] = Order::find($data['order_id'])->buyer_id;
        $data['created_by'] = auth()->id();
        $contract = SalesContract::create($data);

        return back()->with('success', "Sales contract {$contract->contract_number} created successfully.");
    }

    public function update(SalesContractRequest $request, SalesContract $sales_contract): RedirectResponse
    {
        $data = $request->validated();
        $data['buyer_id'] = Order::find($data['order_id'])->buyer_id;
        $sales_contract->update($data);

        return back()->with('success', 'Sales contract updated successfully.');
    }

    public function destroy(SalesContract $sales_contract): RedirectResponse
    {
        $this->authorize('merch_sales_contract.delete');

        $sales_contract->delete();

        return back()->with('success', 'Sales contract deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'ordersOptions' => Order::query()->orderByDesc('id')->limit(200)->get(),
        ];
    }
}
