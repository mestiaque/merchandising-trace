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
            <h4 class="mb-0">
                Inquiry {{ $inquiry->inquiry_no }}
                <span class="badge badge-{{ ['open' => 'primary', 'quoted' => 'info', 'confirmed' => 'success', 'lost' => 'danger', 'cancelled' => 'secondary'][$inquiry->status] ?? 'secondary' }} ml-2">{{ ucfirst($inquiry->status) }}</span>
                @if($inquiry->isOverdue())
                    <span class="badge badge-warning ml-1">Overdue</span>
                @endif
            </h4>
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
                <div class="col-md-3"><strong>Style Ref:</strong> {{ $inquiry->style_ref ?? '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>Color:</strong> {{ $inquiry->color_ref ?? '-' }}</div>
                <div class="col-md-3"><strong>Order Qty:</strong> {{ $inquiry->target_qty !== null ? number_format($inquiry->target_qty) : '-' }}</div>
                <div class="col-md-3"><strong>Unit Price:</strong> {{ $inquiry->target_price !== null ? number_format((float) $inquiry->target_price, 4) : '-' }}</div>
                <div class="col-md-3"><strong>Total Value:</strong> {{ $inquiry->total_value !== null ? number_format((float) $inquiry->total_value, 2) : '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>Ship Date:</strong> {{ $inquiry->target_ship_date?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-3">
                    <strong>Extended Ship Date:</strong> {{ $inquiry->extended_ship_date?->format('Y-m-d') ?? '-' }}
                    @if($inquiry->extended_ship_date)
                        <span class="badge badge-warning">Extended</span>
                    @endif
                </div>
                @if($inquiry->status === 'lost')
                    <div class="col-md-6"><strong>Lost Reason:</strong> {{ $inquiry->lost_reason }}</div>
                @endif
            </div>
            @if($inquiry->description)
                <div class="mb-2"><strong>Description:</strong> @richtext($inquiry->description)</div>
            @endif
            @if($inquiry->remarks)
                <div class="mb-0"><strong>Remarks:</strong> @richtext($inquiry->remarks)</div>
            @endif
        </div>
    </div>

    @if($inquiry->items->isNotEmpty())
        {{-- Lines captured before "one inquiry = one item" — read-only. --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Legacy Inquiry Lines</h6></div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead><tr><th>Style Ref</th><th>Product Type</th><th>Color Ref</th><th>Qty</th><th>Price</th><th>Remarks</th></tr></thead>
                    <tbody>
                        @foreach($inquiry->items as $item)
                            <tr>
                                <td>{{ $item->style_ref }}</td>
                                <td>{{ $item->productType->name ?? '-' }}</td>
                                <td>{{ $item->color_ref }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ $item->target_price }}</td>
                                <td>{{ $item->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Tech Pack</h6>
            @if(! $inquiry->techPack)
                @can('merch_inquiry.edit')
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#convertToStyleModal">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i> Create Tech Pack
                    </button>
                @endcan
            @endif
        </div>
        <div class="card-body">
            @if($inquiry->techPack)
                <a href="{{ route('merchandising-trace.styles.show', $inquiry->techPack) }}" class="badge badge-secondary mr-1">
                    {{ $inquiry->techPack->style_no }} — {{ $inquiry->techPack->name }}
                </a>
            @else
                <span class="text-muted">No tech pack created from this inquiry yet.</span>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Cost Sheets</h6>
            @can('merch_costing.add')
                <a href="{{ route('merchandising-trace.cost-sheets.create', ['inquiry_id' => $inquiry->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-calculator"></i> New Cost Sheet
                </a>
            @endcan
        </div>
        <div class="card-body">
            @forelse($inquiry->costSheets as $cs)
                <a href="{{ route('merchandising-trace.cost-sheets.show', $cs) }}" class="badge badge-info mr-1">{{ $cs->cost_sheet_no }} (v{{ $cs->version }}, {{ ucfirst($cs->status) }})</a>
            @empty
                <span class="text-muted">No cost sheet yet.</span>
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
                        <h5 class="modal-title">Create Tech Pack</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">Buyer, Season, Merchant, Product Type and Description carry over from this inquiry automatically.</div>
                        <div class="row">
<div class="col-md-3 mb-3">
                            <label class="form-label">Style No <span class="text-danger">*</span></label>
                            <input type="text" name="style_no" class="form-control form-control-sm" value="{{ $inquiry->style_ref ?? '' }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Style Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $inquiry->productType->name ?? $inquiry->style_ref ?? '' }}" required>
                        </div>
</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Create Tech Pack</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
