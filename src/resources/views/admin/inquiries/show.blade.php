@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Inquiry ' . $inquiry->inquiry_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Inquiry {{ $inquiry->inquiry_no }}
                <span class="badge bg-{{ ['open' => 'primary', 'quoted' => 'info', 'confirmed' => 'success', 'lost' => 'danger', 'cancelled' => 'secondary'][$inquiry->status] ?? 'secondary' }} ms-2">{{ ucfirst($inquiry->status) }}</span>
                @if($inquiry->isOverdue())
                    <span class="badge bg-warning text-dark ms-1">Overdue</span>
                @endif
            </h5>
            <div>
                @can('merch_inquiry.edit')
                    <a href="{{ route('merchandising-trace.inquiries.edit', $inquiry) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                @endcan
                <a href="{{ route('merchandising-trace.inquiries.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3"><strong>Buyer:</strong> {{ $inquiry->buyer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Season:</strong> {{ $inquiry->season->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Merchant:</strong> {{ $inquiry->merchandiser->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Allocated Fty:</strong> {{ $inquiry->factory->name ?? '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>Given Date:</strong> {{ $inquiry->inquiry_given_date?->format('Y-m-d') }}</div>
                <div class="col-md-3"><strong>Confirmation Due:</strong> {{ $inquiry->order_confirmation_due_date?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-3"><strong>Product Type:</strong> {{ $inquiry->productType->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Target Ship Date:</strong> {{ $inquiry->target_ship_date?->format('Y-m-d') ?? '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>Target Qty:</strong> {{ $inquiry->target_qty ?? '-' }}</div>
                <div class="col-md-3"><strong>Target Price:</strong> {{ $inquiry->target_price ?? '-' }}</div>
                @if($inquiry->status === 'lost')
                    <div class="col-md-6"><strong>Lost Reason:</strong> {{ $inquiry->lost_reason }}</div>
                @endif
            </div>
            @if($inquiry->description)
                <div class="mb-2"><strong>Description:</strong> {{ $inquiry->description }}</div>
            @endif
            @if($inquiry->remarks)
                <div class="mb-0"><strong>Remarks:</strong> {{ $inquiry->remarks }}</div>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Inquiry Items</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Style Ref</th><th>Product Type</th><th>Color Ref</th><th>Qty</th><th>Target Price</th><th>Remarks</th></tr></thead>
                <tbody>
                    @forelse($inquiry->items as $item)
                        <tr>
                            <td>{{ $item->style_ref }}</td>
                            <td>{{ $item->productType->name ?? '-' }}</td>
                            <td>{{ $item->color_ref }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>{{ $item->target_price }}</td>
                            <td>{{ $item->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Styles from this Inquiry</h6>
            @can('merch_inquiry.edit')
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#convertToStyleModal">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i> Convert to Style
                </button>
            @endcan
        </div>
        <div class="card-body">
            @forelse($inquiry->styles as $style)
                <a href="{{ route('merchandising-trace.styles.index') }}" class="badge bg-secondary text-decoration-none me-1">{{ $style->style_no }} — {{ $style->name }}</a>
            @empty
                <span class="text-muted">No styles created from this inquiry yet.</span>
            @endforelse
        </div>
    </div>
</div>

@can('merch_inquiry.edit')
    <div class="modal fade" id="convertToStyleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.inquiries.convert-to-style', $inquiry) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Convert to Style</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">Buyer, Season, Merchant and Product Type carry over from this inquiry automatically.</div>
                        <div class="mb-3">
                            <label class="form-label">Style No <span class="text-danger">*</span></label>
                            <input type="text" name="style_no" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Style Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $inquiry->items->first()->style_ref ?? '' }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Style</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
