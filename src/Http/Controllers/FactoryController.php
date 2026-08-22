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
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.factories.index', ['factories' => $factories]);
    }

    public function store(FactoryRequest $request): RedirectResponse
    {
        Factory::create($request->validated());

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
