<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SizeRequest;
use ME\MerchandisingTrace\Models\Size;

class SizeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_size.list');

        $sizes = Size::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.sizes.index', ['sizes' => $sizes]);
    }

    public function store(SizeRequest $request): RedirectResponse
    {
        Size::create($request->validated());

        return back()->with('success', 'Size created successfully.');
    }

    public function update(SizeRequest $request, Size $size): RedirectResponse
    {
        $size->update($request->validated());

        return back()->with('success', 'Size updated successfully.');
    }

    public function destroy(Size $size): RedirectResponse
    {
        $this->authorize('merch_size.delete');

        $size->delete();

        return back()->with('success', 'Size deleted successfully.');
    }
}
