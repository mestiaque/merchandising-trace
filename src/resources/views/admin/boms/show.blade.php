@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('BOM ' . $bom->bom_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                BOM {{ $bom->bom_no }} <span class="badge bg-secondary">v{{ $bom->version }}</span>
                @php($statusColors = ['draft' => 'secondary', 'submitted' => 'info', 'approved' => 'success', 'revised' => 'dark'])
                <span class="badge bg-{{ $statusColors[$bom->status] ?? 'secondary' }} ms-1">{{ ucfirst($bom->status) }}</span>
            </h5>
            <div>
                @can('merch_bom.edit')
                    @if($bom->status !== 'approved')
                        <a href="{{ route('merchandising-trace.boms.edit', $bom) }}" class="btn btn-outline-primary btn-sm me-1"><i class="fa-solid fa-pen"></i> Edit</a>
                        <form method="POST" action="{{ route('merchandising-trace.boms.approve', $bom) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm me-1"><i class="fa-solid fa-check"></i> Approve</button>
                        </form>
                    @endif
                @endcan
                <a href="{{ route('merchandising-trace.boms.export', $bom) }}" class="btn btn-outline-success btn-sm me-1"><i class="fa-solid fa-file-excel"></i> Export</a>
                @can('merch_bom.edit')
                    <form method="POST" action="{{ route('merchandising-trace.boms.import', $bom) }}" enctype="multipart/form-data" class="d-inline-flex gap-1 me-1">
                        @csrf
                        <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required style="max-width:220px;">
                        <button type="submit" class="btn btn-outline-primary btn-sm text-nowrap">Import Items</button>
                    </form>
                @endcan
                <a href="{{ route('merchandising-trace.boms.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Style:</strong> {{ $bom->style->style_no ?? '-' }} — {{ $bom->style->name ?? '' }}</div>
                <div class="col-md-4"><strong>Buyer:</strong> {{ $bom->style->buyer->name ?? '-' }}</div>
                <div class="col-md-4"><strong>Approved By:</strong> {{ $bom->approver->name ?? '-' }}</div>
            </div>
            @if($bom->remarks)
                <div class="mt-2"><strong>Remarks:</strong> {{ $bom->remarks }}</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">BOM Lines</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr><th>Item</th><th>Type</th><th>Color</th><th>Size</th><th>Part</th><th>Consumption</th><th>Wastage %</th><th>Net Consumption</th><th>Rate</th><th>Supplier</th></tr>
                </thead>
                <tbody>
                    @forelse($bom->items as $line)
                        <tr>
                            <td>{{ $line->item->name ?? '-' }}</td>
                            <td>{{ ucfirst($line->item_type) }}</td>
                            <td>{{ $line->color->name ?? '-' }}</td>
                            <td>{{ $line->size->name ?? '-' }}</td>
                            <td>{{ $line->part_name ?? '-' }}</td>
                            <td>{{ $line->consumption }} {{ $line->uom->name ?? '' }}</td>
                            <td>{{ $line->wastage_percent }}%</td>
                            <td class="fw-bold">{{ number_format($line->netConsumption(), 4) }}</td>
                            <td>{{ $line->rate ?? '-' }}</td>
                            <td>{{ $line->supplier->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted">No lines.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
