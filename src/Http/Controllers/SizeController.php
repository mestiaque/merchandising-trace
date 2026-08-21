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
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.sizes.index', compact('sizes'));
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_size.list');

        $sizes = Size::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('sort_order')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Sizes',
            'columns' => ['name' => 'Name', 'sort_order' => 'Sort Order'],
            'rows'    => $sizes,
        ]);
    }

    public function store(SizeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Size::create($data);

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
