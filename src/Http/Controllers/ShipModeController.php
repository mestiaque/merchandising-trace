<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\ShipModeRequest;
use ME\MerchandisingTrace\Models\ShipMode;

class ShipModeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_ship_mode.list');

        $shipModes = ShipMode::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.ship-modes.index', ['shipModes' => $shipModes]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_ship_mode.list');

        $shipModes = ShipMode::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Ship Modes',
            'columns' => ['name' => 'Name', 'code' => 'Code'],
            'rows'    => $shipModes,
        ]);
    }

    public function store(ShipModeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        ShipMode::create($data);

        return back()->with('success', 'Ship Mode created successfully.');
    }

    public function update(ShipModeRequest $request, ShipMode $shipMode): RedirectResponse
    {
        $shipMode->update($request->validated());

        return back()->with('success', 'Ship Mode updated successfully.');
    }

    public function destroy(ShipMode $shipMode): RedirectResponse
    {
        $this->authorize('merch_ship_mode.delete');

        $shipMode->delete();

        return back()->with('success', 'Ship Mode deleted successfully.');
    }
}
