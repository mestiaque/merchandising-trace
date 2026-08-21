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
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.colors.index', compact('colors'));
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_color.list');

        $colors = Color::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Colors',
            'columns' => ['name' => 'Name', 'code' => 'Code'],
            'rows'    => $colors,
        ]);
    }

    public function store(ColorRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Color::create($data);

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
