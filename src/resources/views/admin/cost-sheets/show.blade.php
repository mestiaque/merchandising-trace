@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Cost Sheet ' . $costSheet->cost_sheet_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Cost Sheet {{ $costSheet->cost_sheet_no }} <span class="badge bg-secondary">v{{ $costSheet->version }}</span>
                @php($statusColors = ['draft' => 'secondary', 'submitted' => 'info', 'approved' => 'success', 'rejected' => 'danger', 'revised' => 'dark'])
                <span class="badge bg-{{ $statusColors[$costSheet->status] ?? 'secondary' }} ms-1">{{ ucfirst($costSheet->status) }}</span>
            </h5>
            <div>
                @can('merch_costing.edit')
                    @if($costSheet->status !== 'approved')
                        <a href="{{ route('merchandising-trace.cost-sheets.edit', $costSheet) }}" class="btn btn-outline-primary btn-sm me-1"><i class="fa-solid fa-pen"></i> Edit</a>
                        <form method="POST" action="{{ route('merchandising-trace.cost-sheets.approve', $costSheet) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm me-1"><i class="fa-solid fa-check"></i> Approve</button>
                        </form>
                    @endif
                @endcan
                <a href="{{ route('merchandising-trace.cost-sheets.print', $costSheet) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm me-1"><i class="fa-solid fa-print"></i> Print</a>
                <a href="{{ route('merchandising-trace.cost-sheets.pdf', $costSheet) }}" class="btn btn-outline-danger btn-sm me-1"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a href="{{ route('merchandising-trace.cost-sheets.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row small">
                <div class="col-md-3"><strong>Tech Pack:</strong>
                    @if($costSheet->style)
                        <a href="{{ route('merchandising-trace.styles.show', $costSheet->style) }}">{{ $costSheet->style->style_no }}</a>
                    @else
                        <span class="text-muted">none</span>
                    @endif
                </div>
                <div class="col-md-3"><strong>Inquiry:</strong>
                    @if($costSheet->inquiry)
                        <a href="{{ route('merchandising-trace.inquiries.show', $costSheet->inquiry) }}">{{ $costSheet->inquiry->inquiry_no }}</a>
                    @else
                        <span class="text-muted">none</span>
                    @endif
                </div>
                <div class="col-md-2"><strong>Price Type:</strong> {{ $costSheet->price_type }}</div>
                <div class="col-md-2"><strong>Buyer Target:</strong> {{ $costSheet->buyer_target_price !== null ? number_format((float) $costSheet->buyer_target_price, 2) : '-' }}</div>
                <div class="col-md-2"><strong>Final Price:</strong> {{ $costSheet->final_price !== null ? number_format((float) $costSheet->final_price, 2) : '-' }}
                    @if($costSheet->final_price)
                        <span class="text-muted">(margin {{ number_format($costSheet->calcMarginPercent(), 2) }}%)</span>
                    @endif
                </div>
            </div>
            <div class="row small mt-1">
                <div class="col-md-3"><strong>Prepared By:</strong> {{ $costSheet->preparer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Approved By:</strong> {{ $costSheet->approver->name ?? '-' }} {{ $costSheet->approved_at ? '(' . $costSheet->approved_at->format('Y-m-d') . ')' : '' }}</div>
            </div>
            @if($costSheet->remarks)
                <div class="mt-2 small"><strong>Remarks:</strong> @richtext($costSheet->remarks)</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <div style="min-width: 900px;">
                    @include('merchandising-trace::admin.cost-sheets.partials.sheet', ['forPdf' => false])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
