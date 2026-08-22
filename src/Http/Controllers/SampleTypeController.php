<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SampleTypeRequest;
use ME\MerchandisingTrace\Models\SampleType;

class SampleTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_sample_type.list');

        $sampleTypes = SampleType::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('sequence')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.sample-types.index', ['sampleTypes' => $sampleTypes]);
    }

    public function store(SampleTypeRequest $request): RedirectResponse
    {
        SampleType::create($request->validated());

        return back()->with('success', 'Sample Type created successfully.');
    }

    public function update(SampleTypeRequest $request, SampleType $sampleType): RedirectResponse
    {
        $sampleType->update($request->validated());

        return back()->with('success', 'Sample Type updated successfully.');
    }

    public function destroy(SampleType $sampleType): RedirectResponse
    {
        $this->authorize('merch_sample_type.delete');

        $sampleType->delete();

        return back()->with('success', 'Sample Type deleted successfully.');
    }
}
