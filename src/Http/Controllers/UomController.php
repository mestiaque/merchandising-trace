<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\UomRequest;
use ME\MerchandisingTrace\Models\Uom;

class UomController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_uom.list');

        $uoms = Uom::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%'))
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.uoms.index', ['uoms' => $uoms]);
    }

    public function store(UomRequest $request): RedirectResponse
    {
        Uom::create($request->validated());

        return back()->with('success', 'UOM created successfully.');
    }

    public function update(UomRequest $request, Uom $uom): RedirectResponse
    {
        $uom->update($request->validated());

        return back()->with('success', 'UOM updated successfully.');
    }

    public function destroy(Uom $uom): RedirectResponse
    {
        $this->authorize('merch_uom.delete');

        $uom->delete();

        return back()->with('success', 'UOM deleted successfully.');
    }
}
