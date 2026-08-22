@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Sales Contract ' . $salesContract->contract_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Sales Contract {{ $salesContract->contract_no }}
                @php($statusColors = ['draft' => 'secondary', 'confirmed' => 'success', 'in_production' => 'info', 'shipped' => 'primary', 'closed' => 'dark', 'cancelled' => 'danger'])
                <span class="badge bg-{{ $statusColors[$salesContract->status] ?? 'secondary' }} ms-1">{{ ucfirst(str_replace('_', ' ', $salesContract->status)) }}</span>
            </h5>
            <div>
                @can('merch_sales_contract.edit')
                    @if($salesContract->status === 'draft')
                        <a href="{{ route('merchandising-trace.sales-contracts.edit', $salesContract) }}" class="btn btn-outline-primary btn-sm me-1"><i class="fa-solid fa-pen"></i> Edit</a>
                        <form method="POST" action="{{ route('merchandising-trace.sales-contracts.confirm', $salesContract) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm me-1" @if($salesContract->pos->isEmpty()) disabled title="Add at least one PO first" @endif><i class="fa-solid fa-check"></i> Confirm</button>
                        </form>
                    @endif
                @endcan
                @can('merch_documentation.list')
                    @if($salesContract->status !== 'draft')
                        <a href="{{ route('merchandising-trace.order-documents.index', $salesContract) }}" class="btn btn-outline-secondary btn-sm me-1"><i class="fa-solid fa-file-lines"></i> Documents</a>
                    @endif
                @endcan
                <a href="{{ route('merchandising-trace.sales-contracts.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3"><strong>Buyer:</strong> {{ $salesContract->buyer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Season:</strong> {{ $salesContract->season->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Merchandiser:</strong> {{ $salesContract->merchandiser->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Factory:</strong> {{ $salesContract->factory->name ?? '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>Contract Date:</strong> {{ $salesContract->contract_date?->format('Y-m-d') }}</div>
                <div class="col-md-3"><strong>Total Qty:</strong> {{ $salesContract->total_qty }}</div>
                <div class="col-md-3"><strong>Total Value:</strong> {{ number_format($salesContract->total_value, 2) }}</div>
                <div class="col-md-3"><strong>Delivery Term:</strong> {{ $salesContract->delivery_term ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">PO Lines</h6>
            @can('merch_sales_contract.add')
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('merchandising-trace.sales-contracts.pos.import', $salesContract) }}" enctype="multipart/form-data" class="d-flex gap-1">
                        @csrf
                        <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap" title="Columns: Style No, Color Code, PO No, PO Qty, Wash Type Code, PCD Date, Shipment Date, Unit Price, Ship Mode Code">Import Excel</button>
                    </form>
                    <a href="{{ route('merchandising-trace.sales-contracts.pos.create', $salesContract) }}" class="btn btn-sm btn-primary text-nowrap"><i class="fa-solid fa-plus"></i> Add PO Line</a>
                </div>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr><th>PO No</th><th>Style</th><th>Color</th><th>Eff. Qty</th><th>Eff. PCD</th><th>Eff. Shipment</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($salesContract->pos as $po)
                        <tr>
                            <td>{{ $po->po_no }}</td>
                            <td>{{ $po->style->style_no ?? '-' }}</td>
                            <td>{{ $po->color->name ?? '-' }}</td>
                            <td>{{ $po->effectiveQty() }} @if(!$po->sizeQtyMatchesEffectiveQty())<span class="badge bg-warning text-dark" title="Size breakdown does not match effective qty">mismatch</span>@endif</td>
                            <td>{{ $po->effectivePcd()?->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $po->effectiveShipment()?->format('Y-m-d') ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $po->status)) }}</span></td>
                            <td class="text-end">
                                @can('merch_sales_contract.edit')
                                    <a href="{{ route('merchandising-trace.sales-contracts.pos.edit', [$salesContract, $po]) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                @endcan
                                @can('merch_sales_contract.delete')
                                    <form method="POST" action="{{ route('merchandising-trace.sales-contracts.pos.destroy', [$salesContract, $po]) }}" class="d-inline" onsubmit="return confirm('Delete this PO line?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No PO lines yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
