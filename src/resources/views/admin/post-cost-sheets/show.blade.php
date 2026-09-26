@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Post Cost ' . $sheet->post_cost_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                Post Cost {{ $sheet->post_cost_no }}
                <span class="badge badge-{{ $sheet->status === 'approved' ? 'success' : 'secondary' }} ml-1">{{ ucfirst($sheet->status) }}</span>
            </h4>
            <div>
                @can('merch_post_costing.edit')
                    @if($sheet->status !== 'approved')
                        <a href="{{ route('merchandising-trace.post-cost-sheets.edit', $sheet) }}" class="btn btn-outline-primary btn-sm mr-1"><i class="fa-solid fa-pen"></i> Edit Actuals</a>
                        <form method="POST" action="{{ route('merchandising-trace.post-cost-sheets.approve', $sheet) }}" class="d-inline"
                            onsubmit="return confirm('Approve and lock {{ $sheet->post_cost_no }}?');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm mr-1"><i class="fa-solid fa-check"></i> Approve</button>
                        </form>
                    @endif
                @endcan
                <a href="{{ route('merchandising-trace.post-cost-sheets.print', $sheet) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm mr-1"><i class="fa-solid fa-print"></i> Print</a>
                @if($sheet->costSheet)
                    <a href="{{ route('merchandising-trace.cost-sheets.show', $sheet->costSheet) }}" class="btn btn-outline-secondary btn-sm mr-1"><i class="fa-solid fa-calculator"></i> Pre-cost</a>
                @endif
                <a href="{{ route('merchandising-trace.post-cost-sheets.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body small">
            <strong>Prepared By:</strong> {{ $sheet->preparer->name ?? '-' }}
            @if($sheet->approver)
                &nbsp;·&nbsp; <strong>Approved By:</strong> {{ $sheet->approver->name }} ({{ optional($sheet->approved_at)->format('d-M-Y') }})
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive"><div style="min-width: 1000px;">
                @include('merchandising-trace::admin.post-cost-sheets.partials.sheet', ['sheet' => $sheet])
            </div></div>
        </div>
    </div>
</div>
@endsection
