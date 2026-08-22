<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\WashTypeRequest;
use ME\MerchandisingTrace\Models\WashType;

class WashTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_wash_type.list');

        $washTypes = WashType::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.wash-types.index', ['washTypes' => $washTypes]);
    }

    public function store(WashTypeRequest $request): RedirectResponse
    {
        WashType::create($request->validated());

        return back()->with('success', 'Wash Type created successfully.');
    }

    public function update(WashTypeRequest $request, WashType $washType): RedirectResponse
    {
        $washType->update($request->validated());

        return back()->with('success', 'Wash Type updated successfully.');
    }

    public function destroy(WashType $washType): RedirectResponse
    {
        $this->authorize('merch_wash_type.delete');

        $washType->delete();

        return back()->with('success', 'Wash Type deleted successfully.');
    }
}
