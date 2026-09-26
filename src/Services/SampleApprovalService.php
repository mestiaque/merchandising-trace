<?php

namespace ME\MerchandisingTrace\Services;

use App\Models\Approval;
use App\Services\ApprovalService;
use Illuminate\Support\Facades\DB;
use ME\MerchandisingTrace\Models\Sample;

/**
 * Sample approval, shared by the sample page and the host's central
 * Approvals page (module 'merchandising.sample', see SampleApprovalHandler):
 *  - submitting a sample raises a central approval request;
 *  - approving writes back into T&A (SampleTnaSyncService);
 *  - rejecting records the buyer comments and opens the next revision.
 * Whichever page decides, the other one is kept in step.
 */
class SampleApprovalService
{
    public const MODULE = 'merchandising.sample';

    public function __construct(
        private SampleTnaSyncService $tna,
        private DocumentNumberService $numbers,
    ) {
    }

    public static function available(): bool
    {
        return class_exists(ApprovalService::class) && class_exists(Approval::class);
    }

    /** Raise the central request for a just-submitted sample (once). */
    public function requestApproval(Sample $sample): void
    {
        if (! static::available() || $this->pending($sample)) {
            return;
        }

        $sample->loadMissing(['style', 'buyer', 'sampleType']);

        app(ApprovalService::class)->request([
            'module' => self::MODULE,
            'approvable' => $sample,
            'title' => "Sample {$sample->sample_no} (" . ($sample->sampleType->name ?? "") . ") - "
                . ($sample->style->style_no ?? '') . ' / ' . ($sample->buyer->name ?? ''),
            'description' => 'Submitted ' . optional($sample->submit_date)->format('d-M-Y')
                . ($sample->revision_no ? " · revision {$sample->revision_no}" : '')
                . ($sample->courier_name ? " · via {$sample->courier_name} {$sample->tracking_no}" : '')
                . '. Approve to update T&A; reject to open the next revision.',
            'route_name' => 'merchandising-trace.samples.show',
            'route_params' => ['sample' => $sample->id],
            'requested_by' => auth()->id(),
        ]);
    }

    /** Returns the number of T&A tasks auto-updated. */
    public function approve(Sample $sample, ?string $comments): int
    {
        $sample->update([
            'status' => 'approved',
            'approval_date' => now(),
            'buyer_comments' => $comments,
        ]);

        return $this->tna->syncFromSample($sample);
    }

    /** Returns the new revision request. */
    public function reject(Sample $sample, string $comments, ?int $userId): Sample
    {
        return DB::transaction(function () use ($sample, $comments, $userId) {
            $sample->update(['status' => 'rejected', 'buyer_comments' => $comments]);

            return Sample::create([
                'sample_no' => $this->numbers->next(Sample::class, 'sample_no', config('merchandising-trace.document_prefixes.sample')),
                'style_id' => $sample->style_id,
                'buyer_id' => $sample->buyer_id,
                'season_id' => $sample->season_id,
                'sample_type_id' => $sample->sample_type_id,
                'merchandiser_id' => $sample->merchandiser_id,
                'order_id' => $sample->order_id,
                'qty' => $sample->qty,
                'size_ref' => $sample->size_ref,
                'color_ref' => $sample->color_ref,
                'request_date' => now(),
                'required_date' => $sample->required_date,
                'status' => 'requested',
                'revision_no' => $sample->revision_no + 1,
                'parent_sample_id' => $sample->id,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Decided on the sample page: close the central request directly (not via
     * ApprovalService, whose handler would apply the decision a second time).
     */
    public function closeCentral(Sample $sample, string $status, ?string $remarks, ?int $userId): void
    {
        if (! static::available()) {
            return;
        }

        Approval::query()->pending()
            ->where('approvable_type', Sample::class)->where('approvable_id', $sample->id)
            ->update(['status' => $status, 'approved_by' => $userId, 'approved_at' => now(), 'remarks' => $remarks]);
    }

    public function pending(Sample $sample): ?Approval
    {
        return static::available()
            ? Approval::query()->pending()->where('approvable_type', Sample::class)->where('approvable_id', $sample->id)->latest('id')->first()
            : null;
    }
}
