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
            ->with(['approver', 'pendingApproval'])
            ->when($request->filled('approval_status'), fn ($q) => $q->where('approval_status', $request->approval_status))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.suppliers.index', ['suppliers' => $suppliers]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated())->submitForApproval();

        return back()->with('success', 'Supplier created and sent for approval — it can be used once approved.');
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        // Editing a rejected supplier is how it gets corrected and re-submitted.
        if ($supplier->approval_status === 'rejected') {
            $supplier->submitForApproval();

            return back()->with('success', 'Supplier updated and re-submitted for approval.');
        }

        return back()->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('merch_supplier.delete');

        $supplier->delete();

        return back()->with('success', 'Supplier deleted successfully.');
    }
}
