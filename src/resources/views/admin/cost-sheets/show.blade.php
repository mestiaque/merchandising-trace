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
                <a href="{{ route('merchandising-trace.cost-sheets.pdf', $costSheet) }}" class="btn btn-outline-danger btn-sm me-1"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a href="{{ route('merchandising-trace.cost-sheets.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3"><strong>Style:</strong> {{ $costSheet->style->style_no ?? '-' }}</div>
                <div class="col-md-3"><strong>Buyer:</strong> {{ $costSheet->buyer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Currency:</strong> {{ $costSheet->currency->code ?? '-' }}</div>
                <div class="col-md-3"><strong>Price Type:</strong> {{ $costSheet->price_type }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>SMV:</strong> {{ $costSheet->smv ?? '-' }}</div>
                <div class="col-md-3"><strong>CM Minute Rate:</strong> {{ $costSheet->cm_minute_rate ?? '-' }}</div>
                <div class="col-md-3"><strong>Efficiency:</strong> {{ $costSheet->efficiency_percent }}%</div>
                <div class="col-md-3"><strong>CM Cost:</strong> {{ number_format($costSheet->cm_cost, 4) }}</div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm w-auto">
                    <tbody>
                        <tr><th>Fabric Cost</th><td>{{ number_format($costSheet->fabric_cost, 4) }}</td></tr>
                        <tr><th>Trims Cost</th><td>{{ number_format($costSheet->trims_cost, 4) }}</td></tr>
                        <tr><th>Accessories Cost</th><td>{{ number_format($costSheet->accessories_cost, 4) }}</td></tr>
                        <tr><th>Print/Emb Cost</th><td>{{ number_format($costSheet->print_emb_cost, 4) }}</td></tr>
                        <tr><th>Wash Cost</th><td>{{ number_format($costSheet->wash_cost, 4) }}</td></tr>
                        <tr><th>CM Cost</th><td>{{ number_format($costSheet->cm_cost, 4) }}</td></tr>
                        <tr><th>Commercial Cost</th><td>{{ number_format($costSheet->commercial_cost, 4) }}</td></tr>
                        <tr><th>Freight Cost</th><td>{{ number_format($costSheet->freight_cost, 4) }}</td></tr>
                        <tr><th>Testing Cost</th><td>{{ number_format($costSheet->testing_cost, 4) }}</td></tr>
                        <tr><th>Overhead Cost</th><td>{{ number_format($costSheet->overhead_cost, 4) }}</td></tr>
                        <tr class="table-active"><th>Total Cost</th><td class="fw-bold">{{ number_format($costSheet->total_cost, 4) }}</td></tr>
                        <tr><th>Profit %</th><td>{{ $costSheet->profit_percent }}%</td></tr>
                        <tr><th>Offer Price</th><td class="fw-bold">{{ number_format($costSheet->offer_price, 4) }}</td></tr>
                        <tr><th>Buyer Target Price</th><td>{{ $costSheet->buyer_target_price ?? '-' }}</td></tr>
                        <tr><th>Final Price</th><td>{{ $costSheet->final_price ?? '-' }}</td></tr>
                        <tr><th>Margin %</th><td>{{ number_format($costSheet->calcMarginPercent(), 2) }}%</td></tr>
                    </tbody>
                </table>
            </div>

            @if($costSheet->remarks)
                <div class="mt-2"><strong>Remarks:</strong> {{ $costSheet->remarks }}</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Cost Lines</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Group</th><th>Item</th><th>Description</th><th>Consumption</th><th>Rate</th><th>Amount</th></tr></thead>
                <tbody>
                    @forelse($costSheet->items as $line)
                        <tr>
                            <td>{{ ucfirst($line->group) }}</td>
                            <td>{{ $line->item->name ?? '-' }}</td>
                            <td>{{ $line->description ?? '-' }}</td>
                            <td>{{ $line->consumption }}</td>
                            <td>{{ $line->rate }}</td>
                            <td class="fw-bold">{{ number_format($line->amount, 4) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No lines.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
