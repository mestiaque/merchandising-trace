<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\ProductTypeRequest;
use ME\MerchandisingTrace\Models\ProductType;

class ProductTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_product_type.list');

        $productTypes = ProductType::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.product-types.index', ['productTypes' => $productTypes]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_product_type.list');

        $productTypes = ProductType::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Product Types',
            'columns' => ['name' => 'Name', 'code' => 'Code', 'category' => 'Category', 'default_smv' => 'Default SMV'],
            'rows'    => $productTypes,
        ]);
    }

    public function store(ProductTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        ProductType::create($data);

        return back()->with('success', 'Product Type created successfully.');
    }

    public function update(ProductTypeRequest $request, ProductType $productType): RedirectResponse
    {
        $productType->update($request->validated());

        return back()->with('success', 'Product Type updated successfully.');
    }

    public function destroy(ProductType $productType): RedirectResponse
    {
        $this->authorize('merch_product_type.delete');

        $productType->delete();

        return back()->with('success', 'Product Type deleted successfully.');
    }
}
