<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\StyleImageTypeRequest;
use ME\MerchandisingTrace\Models\StyleImageType;

class StyleImageTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_style_image_type.list');

        $styleImageTypes = StyleImageType::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.style-image-types.index', ['styleImageTypes' => $styleImageTypes]);
    }

    public function store(StyleImageTypeRequest $request): RedirectResponse
    {
        StyleImageType::create($request->validated());

        return back()->with('success', 'Image Type created successfully.');
    }

    public function update(StyleImageTypeRequest $request, StyleImageType $styleImageType): RedirectResponse
    {
        $styleImageType->update($request->validated());

        return back()->with('success', 'Image Type updated successfully.');
    }

    public function destroy(StyleImageType $styleImageType): RedirectResponse
    {
        $this->authorize('merch_style_image_type.delete');

        $styleImageType->delete();

        return back()->with('success', 'Image Type deleted successfully.');
    }
}
