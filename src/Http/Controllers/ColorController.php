<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\ColorRequest;
use ME\MerchandisingTrace\Models\Color;

class ColorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_color.list');

        $colors = Color::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.colors.index', ['colors' => $colors]);
    }

    public function store(ColorRequest $request): RedirectResponse
    {
        Color::create($request->validated());

        return back()->with('success', 'Color created successfully.');
    }

    public function update(ColorRequest $request, Color $color): RedirectResponse
    {
        $color->update($request->validated());

        return back()->with('success', 'Color updated successfully.');
    }

    public function destroy(Color $color): RedirectResponse
    {
        $this->authorize('merch_color.delete');

        $color->delete();

        return back()->with('success', 'Color deleted successfully.');
    }
}
