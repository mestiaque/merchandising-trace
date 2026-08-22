@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Dashboard') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    <h5 class="mb-3">Merchandising Dashboard</h5>

    {{-- My section — everyone with dashboard access sees this. --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">T&A Due Today</div><div class="h3">{{ $mine['tna_due_today'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">T&A Due This Week</div><div class="h3">{{ $mine['tna_due_this_week'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">T&A Overdue</div><div class="h3 text-danger">{{ $mine['tna_overdue'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">PCD Risk POs</div><div class="h3 text-warning">{{ $mine['pcd_risk'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Samples Pending Approval</div><div class="h3">{{ $mine['samples_pending_approval'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Materials Not Booked (styles)</div><div class="h3">{{ $mine['materials_not_booked'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Shipments This Month</div><div class="h3">{{ $mine['shipments_this_month'] }}</div></div></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-header">My Orders by Status</div>
        <div class="card-body">
            @forelse($mine['orders_by_status'] as $status => $count)
                <span class="badge bg-secondary me-2">{{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $count }}</span>
            @empty
                <span class="text-muted">No orders yet.</span>
            @endforelse
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Production Progress (my POs)</div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>PO</th><th>Cut %</th><th>Sewn %</th><th>Finished %</th><th>Packed %</th><th>Shipped %</th></tr></thead>
                <tbody>
                    @forelse($mine['production_progress'] as $p)
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

    {{-- Management section — only for merch_dashboard.view_all, appended
         below rather than living on a separate page/route. --}}
    @can('merch_dashboard.view_all')
        <hr class="my-4">
        <h5 class="mb-3">Management Overview</h5>

        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">On-time PCD %</div><div class="h3">{{ $management['on_time_pcd_percent'] }}%</div></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">On-time Shipment %</div><div class="h3">{{ $management['on_time_shipment_percent'] }}%</div></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Avg Sample Turnaround (days)</div><div class="h3">{{ $management['avg_sample_turnaround_days'] }}</div></div></div></div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Order Book Value by Buyer</div>
                    <ul class="list-group list-group-flush">
                        @forelse($management['order_book_value_by_buyer'] as $buyer => $value)
                            <li class="list-group-item d-flex justify-content-between"><span>{{ $buyer }}</span><span>{{ number_format($value, 2) }}</span></li>
                        @empty
                            <li class="list-group-item text-muted">No data.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header">Order Book Value by Season</div>
                    <ul class="list-group list-group-flush">
                        @forelse($management['order_book_value_by_season'] as $season => $value)
                            <li class="list-group-item d-flex justify-content-between"><span>{{ $season }}</span><span>{{ number_format($value, 2) }}</span></li>
                        @empty
                            <li class="list-group-item text-muted">No data.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Delay Reasons Pareto (top 10)</div>
                    <ul class="list-group list-group-flush">
                        @forelse($management['delay_reasons_pareto'] as $reason => $count)
                            <li class="list-group-item d-flex justify-content-between"><span>{{ $reason }}</span><span>{{ $count }}</span></li>
                        @empty
                            <li class="list-group-item text-muted">No delays recorded.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header">PCD Failures by Responsible Department</div>
                    <ul class="list-group list-group-flush">
                        @forelse($management['pcd_failures_by_dept'] as $dept => $count)
                            <li class="list-group-item d-flex justify-content-between"><span>{{ $dept }}</span><span>{{ $count }}</span></li>
                        @empty
                            <li class="list-group-item text-muted">No PCD failures.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="alert alert-secondary mt-3">
            Note: "capacity vs booked qty by month/factory" is not shown here — no factory capacity-planning data source exists anywhere else in this build to draw it from.
        </div>
    @endcan
</div>
@endsection
