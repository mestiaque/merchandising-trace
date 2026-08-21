<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SupplierRequest;
use ME\MerchandisingTrace\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_supplier.list');

        $suppliers = Supplier::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.suppliers.index', ['suppliers' => $suppliers]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_supplier.list');

        $suppliers = Supplier::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Suppliers',
            'columns' => ['name' => 'Name', 'code' => 'Code', 'phone' => 'Phone'],
            'rows'    => $suppliers,
        ]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Supplier::create($data);

        return back()->with('success', 'Supplier created successfully.');
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return back()->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('merch_supplier.delete');

        $supplier->delete();

        return back()->with('success', 'Supplier deleted successfully.');
    }
}
