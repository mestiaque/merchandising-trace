@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('My Dashboard') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    <h5 class="mb-3">My Dashboard</h5>
    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">T&A Due Today</div><div class="h3">{{ $data['tna_due_today'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">T&A Due This Week</div><div class="h3">{{ $data['tna_due_this_week'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">T&A Overdue</div><div class="h3 text-danger">{{ $data['tna_overdue'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">PCD Risk POs</div><div class="h3 text-warning">{{ $data['pcd_risk'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Samples Pending Approval</div><div class="h3">{{ $data['samples_pending_approval'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Materials Not Booked (styles)</div><div class="h3">{{ $data['materials_not_booked'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Shipments This Month</div><div class="h3">{{ $data['shipments_this_month'] }}</div></div></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Orders by Status</div>
        <div class="card-body">
            @forelse($data['orders_by_status'] as $status => $count)
                <span class="badge bg-secondary me-2">{{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $count }}</span>
            @empty
                <span class="text-muted">No orders yet.</span>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header">Production Progress (my POs)</div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>PO</th><th>Cut %</th><th>Sewn %</th><th>Finished %</th><th>Packed %</th><th>Shipped %</th></tr></thead>
                <tbody>
                    @forelse($data['production_progress'] as $p)
                        <tr>
                            <td>{{ $p->salesContractPo->po_no ?? '-' }}</td>
                            <td>{{ $p->cutPercent() }}%</td>
                            <td>{{ $p->sewnPercent() }}%</td>
                            <td>{{ $p->finishedPercent() }}%</td>
                            <td>{{ $p->packedPercent() }}%</td>
                            <td>{{ $p->shippedPercent() }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No production progress synced yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
