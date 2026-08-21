<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\FactoryRequest;
use ME\MerchandisingTrace\Models\Factory;

class FactoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_factory.list');

        $factories = Factory::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.factories.index', ['factories' => $factories]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_factory.list');

        $factories = Factory::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Factories',
            'columns' => ['name' => 'Name', 'code' => 'Code', 'unit_type' => 'Unit Type', 'capacity_per_month' => 'Capacity/Month'],
            'rows'    => $factories,
        ]);
    }

    public function store(FactoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Factory::create($data);

        return back()->with('success', 'Factory created successfully.');
    }

    public function update(FactoryRequest $request, Factory $factory): RedirectResponse
    {
        $factory->update($request->validated());

        return back()->with('success', 'Factory updated successfully.');
    }

    public function destroy(Factory $factory): RedirectResponse
    {
        $this->authorize('merch_factory.delete');

        $factory->delete();

        return back()->with('success', 'Factory deleted successfully.');
    }
}
