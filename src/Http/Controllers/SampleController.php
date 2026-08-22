<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SampleRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SampleType;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;

class SampleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_sample.list');

        $samples = Sample::query()
            ->with(['buyer', 'style', 'sampleType'])
            ->when($request->filled('buyer_id'), fn ($q) => $q->where('buyer_id', $request->buyer_id))
            ->when($request->filled('sample_type_id'), fn ($q) => $q->where('sample_type_id', $request->sample_type_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Board columns — grouped counts per status for the Kanban-style summary.
        $boardCounts = Sample::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('merchandising-trace::admin.samples.index', ['samples' => $samples, 'boardCounts' => $boardCounts] + $this->formOptions());
    }

    public function printList(Request $request): View
    {
        $this->authorize('merch_sample.list');

        $samples = Sample::query()
            ->with(['buyer', 'style'])
            ->when($request->filled('buyer_id'), fn ($q) => $q->where('buyer_id', $request->buyer_id))
            ->when($request->filled('sample_type_id'), fn ($q) => $q->where('sample_type_id', $request->sample_type_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Samples',
            'columns' => ['sample_number' => 'Sample No', 'buyer.name' => 'Buyer', 'style.name' => 'Style', 'qty' => 'Qty', 'status' => 'Status'],
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

        return redirect()->route('merchandising-trace.samples.show', $sample)->with('success', "Sample {$sample->sample_number} created successfully.");
    }

    public function show(Sample $sample): View
    {
        $this->authorize('merch_sample.view');

        $sample->load(['buyer', 'style', 'size', 'sampleType', 'merchandiser', 'season', 'parentSample', 'revisions', 'comments.commenter']);

        return view('merchandising-trace::admin.samples.show', compact('sample'));
    }

    public function print(Sample $sample): View
    {
        $this->authorize('merch_sample.view');

        $sample->load(['buyer', 'style', 'size', 'sampleType']);

        return view('merchandising-trace::admin.partials.print-detail', [
            'title'     => 'Sample',
            'docNumber' => $sample->sample_number,
            'meta'      => [
                'Buyer'           => $sample->buyer->name ?? '-',
                'Style'           => $sample->style->name ?? '-',
                'Type'            => $sample->sampleType->name ?? '-',
                'Qty'             => $sample->qty,
                'Size'            => $sample->size->name ?? $sample->size_ref ?? '-',
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

        return redirect()->route('merchandising-trace.samples.show', $sample)->with('success', "Sample {$sample->sample_number} updated successfully.");
    }

    public function destroy(Sample $sample): RedirectResponse
    {
        $this->authorize('merch_sample.delete');

        $sample->delete();

        return back()->with('success', 'Sample deleted successfully.');
    }

    /**
     * §M04: submit form (courier + tracking).
     */
    public function submit(Request $request, Sample $sample): RedirectResponse
    {
        $this->authorize('merch_sample.edit');

        $request->validate([
            'submission_date' => ['required', 'date'],
            'courier_name' => ['nullable', 'string', 'max:150'],
            'tracking_no' => ['nullable', 'string', 'max:150'],
        ]);

        $sample->update([
            'submission_date' => $request->submission_date,
            'courier_name' => $request->courier_name,
            'tracking_no' => $request->tracking_no,
            'status' => 'submitted',
        ]);

        return back()->with('success', 'Sample marked as submitted.');
    }

    /**
     * §M04: approval form (buyer comment). Rejection auto-creates the next
     * revision request via resubmit(); approval just records the date/comment
     * — the T&A auto-sync write-back happens once the T&A module exists.
     */
    public function approve(Request $request, Sample $sample): RedirectResponse
    {
        $this->authorize('merch_sample.edit');

        $request->validate(['buyer_comments' => ['nullable', 'string']]);

        $sample->update([
            'status' => 'approved',
            'approval_date' => now(),
            'buyer_comments' => $request->buyer_comments,
        ]);

        return back()->with('success', 'Sample approved.');
    }

    public function reject(Request $request, Sample $sample): RedirectResponse
    {
        $this->authorize('merch_sample.edit');

        $request->validate(['buyer_comments' => ['required', 'string']]);

        $sample->update([
            'status' => 'rejected',
            'buyer_comments' => $request->buyer_comments,
        ]);

        return back()->with('success', 'Sample rejected.');
    }

    /**
     * Rejection/resubmission auto-creates the next revision as a new sample
     * row chained via parent_sample_id — the original row is left as the
     * historical record (§4.4 revision_no / parent_sample_id).
     */
    public function resubmit(Sample $sample): RedirectResponse
    {
        $this->authorize('merch_sample.add');

        $revision = DB::transaction(function () use ($sample) {
            $sample->update(['status' => 'resubmit']);

            return Sample::create([
                'buyer_id' => $sample->buyer_id,
                'style_id' => $sample->style_id,
                'order_id' => $sample->order_id,
                'season_id' => $sample->season_id,
                'merchandiser_id' => $sample->merchandiser_id,
                'sample_type_id' => $sample->sample_type_id,
                'qty' => $sample->qty,
                'size_id' => $sample->size_id,
                'size_ref' => $sample->size_ref,
                'color_ref' => $sample->color_ref,
                'request_date' => now(),
                'required_date' => $sample->required_date,
                'status' => 'requested',
                'revision_no' => $sample->revision_no + 1,
                'parent_sample_id' => $sample->id,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('merchandising-trace.samples.show', $revision)->with('success', "Revision {$revision->sample_number} (rev {$revision->revision_no}) created.");
    }

    public function addComment(Request $request, Sample $sample): RedirectResponse
    {
        $this->authorize('merch_sample.edit');

        $request->validate([
            'comment' => ['required', 'string'],
            'is_buyer_comment' => ['nullable', 'boolean'],
        ]);

        $sample->comments()->create([
            'comment' => $request->comment,
            'commented_by' => auth()->id(),
            'comment_date' => now(),
            'is_buyer_comment' => $request->boolean('is_buyer_comment'),
        ]);

        return back()->with('success', 'Comment added.');
    }

    private function formOptions(): array
    {
        return [
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'stylesOptions' => Style::query()->active()->orderBy('name')->get(),
            'sizesOptions'  => Size::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
            'sampleTypesOptions' => SampleType::query()->active()->orderBy('sequence')->get(),
            'seasonsOptions' => Season::query()->active()->orderBy('name')->get(),
            'merchandisersOptions' => User::query()->orderBy('name')->get(),
        ];
    }
}
