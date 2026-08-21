<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\IncotermRequest;
use ME\MerchandisingTrace\Models\Incoterm;

class IncotermController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_incoterm.list');

        $incoterms = Incoterm::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.incoterms.index', ['incoterms' => $incoterms]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_incoterm.list');

        $incoterms = Incoterm::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Incoterms',
            'columns' => ['name' => 'Name', 'code' => 'Code'],
            'rows'    => $incoterms,
        ]);
    }

    public function store(IncotermRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Incoterm::create($data);

        return back()->with('success', 'Incoterm created successfully.');
    }

    public function update(IncotermRequest $request, Incoterm $incoterm): RedirectResponse
    {
        $incoterm->update($request->validated());

        return back()->with('success', 'Incoterm updated successfully.');
    }

    public function destroy(Incoterm $incoterm): RedirectResponse
    {
        $this->authorize('merch_incoterm.delete');

        $incoterm->delete();

        return back()->with('success', 'Incoterm deleted successfully.');
    }
}
