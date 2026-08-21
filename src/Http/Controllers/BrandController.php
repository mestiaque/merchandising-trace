<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\BrandRequest;
use ME\MerchandisingTrace\Models\Brand;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_brand.list');

        $brands = Brand::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.brands.index', ['brands' => $brands]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_brand.list');

        $brands = Brand::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Brands',
            'columns' => ['name' => 'Name', 'code' => 'Code'],
            'rows'    => $brands,
        ]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Brand::create($data);

        return back()->with('success', 'Brand created successfully.');
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return back()->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('merch_brand.delete');

        $brand->delete();

        return back()->with('success', 'Brand deleted successfully.');
    }
}
