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
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.uoms.index', ['uoms' => $uoms]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_uom.list');

        $uoms = Uom::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Unit of Measure',
            'columns' => ['name' => 'Name', 'short_name' => 'Short Name'],
            'rows'    => $uoms,
        ]);
    }

    public function store(UomRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Uom::create($data);

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
