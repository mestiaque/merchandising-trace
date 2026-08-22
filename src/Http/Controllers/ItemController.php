<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\ItemRequest;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\ItemCategory;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\Uom;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_item.list');

        $items = Item::query()
            ->with(['category', 'uom'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.items.index', [
            'items' => $items,
            'categoriesOptions' => ItemCategory::query()->active()->orderBy('name')->get(),
            'uomsOptions' => Uom::query()->active()->orderBy('name')->get(),
            'suppliersOptions' => Supplier::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        Item::create($request->validated());

        return back()->with('success', 'Item created successfully.');
    }

    public function update(ItemRequest $request, Item $item): RedirectResponse
    {
        $item->update($request->validated());

        return back()->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $this->authorize('merch_item.delete');

        $item->delete();

        return back()->with('success', 'Item deleted successfully.');
    }
}
