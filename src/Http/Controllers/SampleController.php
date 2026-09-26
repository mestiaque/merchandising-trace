<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SampleRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SampleType;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Services\DocumentNumberService;
use ME\MerchandisingTrace\Services\SampleApprovalService;

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

        // Kanban-lite status board counts.
        $boardCounts = Sample::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('merchandising-trace::admin.samples.index', ['samples' => $samples, 'boardCounts' => $boardCounts] + $this->formOptions());
    }

    public function create(): View
    {
        $this->authorize('merch_sample.add');

        return view('merchandising-trace::admin.samples.create', $this->formOptions());
    }

    public function store(SampleRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();
        $data['sample_no'] = $numbers->next(Sample::class, 'sample_no', config('merchandising-trace.document_prefixes.sample'));
        $data['created_by'] = auth()->id();
        $sample = Sample::create($data);

        return redirect()->route('merchandising-trace.samples.show', $sample)->with('success', "Sample {$sample->sample_no} created successfully.");
    }

    public function show(Sample $sample): View
    {
        $this->authorize('merch_sample.view');

        $sample->load(['buyer', 'style', 'sampleType', 'merchandiser', 'season', 'parentSample', 'revisions', 'comments.commenter']);

        return view('merchandising-trace::admin.samples.show', compact('sample'));
    }

    public function edit(Sample $sample): View
    {
        $this->authorize('merch_sample.edit');

        return view('merchandising-trace::admin.samples.edit', ['sample' => $sample] + $this->formOptions());
    }

    public function update(SampleRequest $request, Sample $sample): RedirectResponse
    {
        $sample->update($request->validated());

        app(\ME\MerchandisingTrace\Services\SampleTnaSyncService::class)->syncFromSample($sample);

        return redirect()->route('merchandising-trace.samples.show', $sample)->with('success', "Sample {$sample->sample_no} updated successfully.");
    }

    public function destroy(Sample $sample): RedirectResponse
    {
        $this->authorize('merch_sample.delete');

        app(SampleApprovalService::class)->closeCentral($sample, 'cancelled', 'Sample deleted', auth()->id());
        $sample->delete();

        return back()->with('success', 'Sample deleted successfully.');
    }

    /**
     * §M04: submit form (courier + tracking).
     */
    public function submit(Request $request, Sample $sample, SampleApprovalService $approvals): RedirectResponse
    {
        $this->authorize('merch_sample.edit');

        $request->validate([
            'submit_date' => ['required', 'date'],
            'courier_name' => ['nullable', 'string', 'max:150'],
            'tracking_no' => ['nullable', 'string', 'max:150'],
        ]);

        $sample->update([
            'submit_date' => $request->submit_date,
            'courier_name' => $request->courier_name,
            'tracking_no' => $request->tracking_no,
            'status' => 'submitted',
        ]);

        app(\ME\MerchandisingTrace\Services\SampleTnaSyncService::class)->syncFromSample($sample);
        $approvals->requestApproval($sample);

        return back()->with('success', 'Sample marked as submitted and sent for approval.');
    }

    /**
     * §M04 + §8.4 AC: approving a sample instantly writes back into every
     * matching T&A task (e.g. "1st PP Approval") via SampleTnaSyncService —
     * those cells are read-only in the grid once auto-filled.
     */
    public function approve(Request $request, Sample $sample, SampleApprovalService $approvals): RedirectResponse
    {
        $this->authorize('merch_sample.approve');

        $request->validate(['buyer_comments' => ['nullable', 'string']]);

        $synced = $approvals->approve($sample, $request->buyer_comments);
        $approvals->closeCentral($sample, 'approved', $request->buyer_comments, auth()->id());

        return back()->with('success', 'Sample approved.' . ($synced ? " {$synced} T&A task(s) auto-updated." : ''));
    }

    /**
     * §M04: rejection auto-creates the next revision request.
     */
    public function reject(Request $request, Sample $sample, SampleApprovalService $approvals): RedirectResponse
    {
        $this->authorize('merch_sample.approve');

        $request->validate(['buyer_comments' => ['required', 'string']]);

        $revision = $approvals->reject($sample, $request->buyer_comments, auth()->id());
        $approvals->closeCentral($sample, 'rejected', $request->buyer_comments, auth()->id());

        return redirect()->route('merchandising-trace.samples.show', $revision)->with('success', "Revision {$revision->sample_no} (rev {$revision->revision_no}) created.");
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
            'sampleTypesOptions' => SampleType::query()->active()->orderBy('sequence')->get(),
            'seasonsOptions' => Season::query()->active()->orderBy('name')->get(),
            'merchandisersOptions' => User::query()->orderBy('name')->get(),
        ];
    }
}
