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
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.ship-modes.index', ['shipModes' => $shipModes]);
    }

    public function store(ShipModeRequest $request): RedirectResponse
    {
        ShipMode::create($request->validated());

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
