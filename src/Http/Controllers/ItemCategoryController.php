<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\ItemCategoryRequest;
use ME\MerchandisingTrace\Models\ItemCategory;

class ItemCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_item_category.list');

        $itemCategories = ItemCategory::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.item-categories.index', ['itemCategories' => $itemCategories]);
    }

    public function store(ItemCategoryRequest $request): RedirectResponse
    {
        ItemCategory::create($request->validated());

        return back()->with('success', 'Item Category created successfully.');
    }

    public function update(ItemCategoryRequest $request, ItemCategory $itemCategory): RedirectResponse
    {
        $itemCategory->update($request->validated());

        return back()->with('success', 'Item Category updated successfully.');
    }

    public function destroy(ItemCategory $itemCategory): RedirectResponse
    {
        $this->authorize('merch_item_category.delete');

        $itemCategory->delete();

        return back()->with('success', 'Item Category deleted successfully.');
    }
}
