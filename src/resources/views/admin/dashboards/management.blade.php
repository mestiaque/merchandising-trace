@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Management Dashboard') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    <h5 class="mb-3">Management Dashboard</h5>
    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">On-time PCD %</div><div class="h3">{{ $data['on_time_pcd_percent'] }}%</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">On-time Shipment %</div><div class="h3">{{ $data['on_time_shipment_percent'] }}%</div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Avg Sample Turnaround (days)</div><div class="h3">{{ $data['avg_sample_turnaround_days'] }}</div></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Order Book Value by Buyer</div>
                <ul class="list-group list-group-flush">
                    @forelse($data['order_book_value_by_buyer'] as $buyer => $value)
                        <li class="list-group-item d-flex justify-content-between"><span>{{ $buyer }}</span><span>{{ number_format($value, 2) }}</span></li>
                    @empty
                        <li class="list-group-item text-muted">No data.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card">
                <div class="card-header">Order Book Value by Season</div>
                <ul class="list-group list-group-flush">
                    @forelse($data['order_book_value_by_season'] as $season => $value)
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
                    @forelse($data['delay_reasons_pareto'] as $reason => $count)
                        <li class="list-group-item d-flex justify-content-between"><span>{{ $reason }}</span><span>{{ $count }}</span></li>
                    @empty
                        <li class="list-group-item text-muted">No delays recorded.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card">
                <div class="card-header">PCD Failures by Responsible Department</div>
                <ul class="list-group list-group-flush">
                    @forelse($data['pcd_failures_by_dept'] as $dept => $count)
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
</div>
@endsection
