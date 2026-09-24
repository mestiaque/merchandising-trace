@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Sample ' . $sample->sample_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Sample — {{ $sample->sample_no }}
                @if($sample->revision_no > 1)<span class="badge bg-dark">rev {{ $sample->revision_no }}</span>@endif
                @php($statusColors = ['requested' => 'secondary', 'in_progress' => 'warning', 'submitted' => 'info', 'approved' => 'success', 'rejected' => 'danger', 'resubmit' => 'dark', 'cancelled' => 'secondary'])
                <span class="badge bg-{{ $statusColors[$sample->status] ?? 'secondary' }} ms-1">{{ ucfirst(str_replace('_', ' ', $sample->status)) }}</span>
            </h5>
            <div>
                @can('merch_sample.edit')
                    <a href="{{ route('merchandising-trace.samples.edit', $sample) }}" class="btn btn-outline-primary btn-sm me-1"><i class="fa-solid fa-pen"></i> Edit</a>
                @endcan
                <a href="{{ route('merchandising-trace.samples.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2"><strong>Buyer:</strong> {{ $sample->buyer->name ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Style:</strong> {{ $sample->style->style_no ?? '-' }} — {{ $sample->style->name ?? '' }}</div>
                <div class="col-md-3 mb-2"><strong>Type:</strong> {{ $sample->sampleType->name ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Qty:</strong> {{ $sample->qty }}</div>
                <div class="col-md-3 mb-2"><strong>Season:</strong> {{ $sample->season->name ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Merchandiser:</strong> {{ $sample->merchandiser->name ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Size Ref:</strong> {{ $sample->size_ref ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Color Ref:</strong> {{ $sample->color_ref ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Request Date:</strong> {{ optional($sample->request_date)->format('d M Y') ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Required Date:</strong> {{ optional($sample->required_date)->format('d M Y') ?? '-' }}</div>
                <div class="col-md-3 mb-2">
                    <strong>Submit Date:</strong> {{ optional($sample->submit_date)->format('d M Y') ?? '-' }}
                    @if($sample->isLateSubmission())<span class="badge bg-danger">Late</span>@endif
                </div>
                <div class="col-md-3 mb-2"><strong>Approval Date:</strong> {{ optional($sample->approval_date)->format('d M Y') ?? '-' }}</div>
                @if($sample->courier_name || $sample->tracking_no)
                    <div class="col-md-6 mb-2"><strong>Courier:</strong> {{ $sample->courier_name ?? '-' }} / {{ $sample->tracking_no ?? '-' }}</div>
                @endif
                @if($sample->buyer_comments)
                    <div class="col-12 mb-2"><strong>Buyer Comments:</strong> @richtext($sample->buyer_comments)</div>
                @endif
                @if($sample->remarks)
                    <div class="col-12 mb-2"><strong>Remarks:</strong> @richtext($sample->remarks)</div>
                @endif
                @if($sample->parentSample)
                    <div class="col-12 mb-2"><strong>Revised from:</strong> {{ $sample->parentSample->sample_no }}</div>
                @endif
            </div>

            @can('merch_sample.edit')
                <hr>
                <div class="d-flex flex-wrap gap-2">
                    @if(!in_array($sample->status, ['submitted', 'approved']))
                        <button type="button" class="btn btn-sm btn-info text-white" data-toggle="modal" data-target="#submitModal"><i class="fa-solid fa-paper-plane"></i> Submit</button>
                    @endif
                    @if(in_array($sample->status, ['submitted', 'in_progress']))
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#approveModal"><i class="fa-solid fa-check"></i> Approve</button>
                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal"><i class="fa-solid fa-xmark"></i> Reject (creates revision)</button>
                    @endif
                </div>
            @endcan
        </div>
    </div>

    @if($sample->parentSample || $sample->revisions->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Revision Chain</h6></div>
            <div class="card-body">
                @php($chain = collect([$sample])->when($sample->parentSample, fn($c) => $c->prepend($sample->parentSample))->merge($sample->revisions))
                @foreach($chain->sortBy('revision_no') as $rev)
                    <span class="badge {{ $rev->id === $sample->id ? 'bg-primary' : 'bg-secondary' }} me-1">rev {{ $rev->revision_no }} — {{ ucfirst(str_replace('_', ' ', $rev->status)) }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Comments</h6></div>
        <div class="card-body">
            @forelse($sample->comments as $comment)
                <div class="border-bottom pb-2 mb-2">
                    <div class="small text-muted">
                        {{ $comment->commenter->name ?? 'System' }} — {{ $comment->comment_date->format('d M Y') }}
                        @if($comment->is_buyer_comment)<span class="badge bg-info text-dark">Buyer</span>@endif
                    </div>
                    <div>@richtext($comment->comment)</div>
                </div>
            @empty
                <p class="text-muted mb-0">No comments yet.</p>
            @endforelse

            @can('merch_sample.edit')
                <form method="POST" action="{{ route('merchandising-trace.samples.comments.store', $sample) }}" class="mt-3">
                    @csrf
                    <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Add a comment..." required></textarea>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_buyer_comment" value="1" class="form-check-input" id="isBuyerComment">
                        <label class="form-check-label" for="isBuyerComment">This is a buyer comment</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Add Comment</button>
                </form>
            @endcan
        </div>
    </div>
</div>

@can('merch_sample.edit')
    <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.samples.submit', $sample) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Submit Sample</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Submit Date <span class="text-danger">*</span></label><input type="date" name="submit_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                        <div class="mb-3"><label class="form-label">Courier Name</label><input type="text" name="courier_name" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Tracking No</label><input type="text" name="tracking_no" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.samples.approve', $sample) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Sample</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Buyer Comments</label><textarea name="buyer_comments" class="form-control" rows="2"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.samples.reject', $sample) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Sample</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Buyer Comments <span class="text-danger">*</span></label><textarea name="buyer_comments" class="form-control" rows="2" required></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject & Create Revision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
