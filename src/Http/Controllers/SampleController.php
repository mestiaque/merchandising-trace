<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SampleRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;

class SampleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_sample.list');

        $samples = Sample::query()
            ->with(['buyer', 'style'])
            ->when($request->filled('buyer_id'), fn ($q) => $q->where('buyer_id', $request->buyer_id))
            ->when($request->filled('sample_type'), fn ($q) => $q->where('sample_type', $request->sample_type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.samples.index', ['samples' => $samples] + $this->formOptions());
    }

    public function printList(Request $request): View
    {
        $this->authorize('merch_sample.list');

        $samples = Sample::query()
            ->with(['buyer', 'style'])
            ->when($request->filled('buyer_id'), fn ($q) => $q->where('buyer_id', $request->buyer_id))
            ->when($request->filled('sample_type'), fn ($q) => $q->where('sample_type', $request->sample_type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Samples',
            'columns' => ['sample_number' => 'Sample No', 'buyer.name' => 'Buyer', 'style.name' => 'Style', 'sample_type' => 'Type', 'qty' => 'Qty', 'status' => 'Status'],
            'rows'    => $samples,
        ]);
    }

    public function create(): View
    {
        $this->authorize('merch_sample.add');

        return view('merchandising-trace::admin.samples.create', $this->formOptions());
    }

    public function store(SampleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $sample = Sample::create($data);

        return redirect()->route('merchandising-trace.samples.index')->with('success', "Sample {$sample->sample_number} created successfully.");
    }

    public function show(Sample $sample): View
    {
        $this->authorize('merch_sample.view');

        $sample->load(['buyer', 'style', 'size']);

        return view('merchandising-trace::admin.samples.show', compact('sample'));
    }

    public function print(Sample $sample): View
    {
        $this->authorize('merch_sample.view');

        $sample->load(['buyer', 'style', 'size']);

        return view('merchandising-trace::admin.partials.print-detail', [
            'title'     => 'Sample',
            'docNumber' => $sample->sample_number,
            'meta'      => [
                'Buyer'           => $sample->buyer->name ?? '-',
                'Style'           => $sample->style->name ?? '-',
                'Type'            => ucfirst(str_replace('_', ' ', $sample->sample_type)),
                'Qty'             => $sample->qty,
                'Size'            => $sample->size->name ?? '-',
                'Status'          => ucfirst(str_replace('_', ' ', $sample->status)),
                'Request Date'    => optional($sample->request_date)->format('d M Y') ?? '-',
                'Submission Date' => optional($sample->submission_date)->format('d M Y') ?? '-',
                'Approval Date'   => optional($sample->approval_date)->format('d M Y') ?? '-',
            ],
            'remarks' => $sample->remarks,
        ]);
    }

    public function edit(Sample $sample): View
    {
        $this->authorize('merch_sample.edit');

        return view('merchandising-trace::admin.samples.edit', ['sample' => $sample] + $this->formOptions());
    }

    public function update(SampleRequest $request, Sample $sample): RedirectResponse
    {
        $sample->update($request->validated());

        return redirect()->route('merchandising-trace.samples.index')->with('success', "Sample {$sample->sample_number} updated successfully.");
    }

    public function destroy(Sample $sample): RedirectResponse
    {
        $this->authorize('merch_sample.delete');

        $sample->delete();

        return back()->with('success', 'Sample deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'stylesOptions' => Style::query()->active()->orderBy('name')->get(),
            'sizesOptions'  => Size::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }
}
