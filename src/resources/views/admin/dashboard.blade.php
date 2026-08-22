@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Merchandising Dashboard') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <style>
    .merch-stat-card { background: #fff; border-radius: 12px; padding: 20px 18px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); border: none; transition: transform .2s, box-shadow .2s; height: 100%; }
    .merch-stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.11); }
    .merch-stat-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .merch-stat-val { font-size: 24px; font-weight: 700; line-height: 1; margin-bottom: 3px; }
    .merch-stat-lbl { font-size: 12px; color: #888; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }
    .merch-section-title { font-size: 13px; font-weight: 700; color: #444; text-transform: uppercase; letter-spacing: 1px; border-left: 3px solid #b45309; padding-left: 10px; margin-bottom: 16px; }
    .merch-chart-card { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 12px rgba(0,0,0,.07); height: 100%; }
    .merch-quick-btn { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; background: #fdf3e8; border: 1px solid #f0dcc0; color: #444; font-size: 13px; font-weight: 500; text-decoration: none; transition: all .2s; }
    .merch-quick-btn:hover { background: #b45309; color: #fff; border-color: #b45309; }
    .merch-quick-btn i { width: 20px; text-align: center; }
    .merch-recent-table td { font-size: 13px; vertical-align: middle; padding: 8px 10px; }
    </style>

    {{-- ── Section Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 mt-1">
        <h4 class="mb-0" style="font-size:17px;font-weight:700;">
            <i class="fa-solid fa-shirt me-2" style="color:#b45309;"></i> Merchandising Dashboard
        </h4>
    </div>

    {{-- ── My Stat Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#eef2f9;"><i class="fa-solid fa-calendar-day" style="color:#2a4b7c;"></i></div>
                <div><div class="merch-stat-val" style="color:#2a4b7c;">{{ $mine['tna_due_today'] }}</div><div class="merch-stat-lbl">T&amp;A Due Today</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f0f9ff;"><i class="fa-solid fa-calendar-week" style="color:#0ea5e9;"></i></div>
                <div><div class="merch-stat-val" style="color:#0ea5e9;">{{ $mine['tna_due_this_week'] }}</div><div class="merch-stat-lbl">Due This Week</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:{{ $mine['tna_overdue'] > 0 ? '#fff1f2' : '#ecfdf5' }};"><i class="fa-solid fa-triangle-exclamation" style="color:{{ $mine['tna_overdue'] > 0 ? '#f43f5e' : '#10b981' }};"></i></div>
                <div><div class="merch-stat-val" style="color:{{ $mine['tna_overdue'] > 0 ? '#f43f5e' : '#10b981' }};">{{ $mine['tna_overdue'] }}</div><div class="merch-stat-lbl">T&amp;A Overdue</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:{{ $mine['pcd_risk'] > 0 ? '#fff7ed' : '#ecfdf5' }};"><i class="fa-solid fa-flag" style="color:{{ $mine['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};"></i></div>
                <div><div class="merch-stat-val" style="color:{{ $mine['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};">{{ $mine['pcd_risk'] }}</div><div class="merch-stat-lbl">PCD Risk POs</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f5f0fb;"><i class="fa-solid fa-vial" style="color:#7c3aed;"></i></div>
                <div><div class="merch-stat-val" style="color:#7c3aed;">{{ $mine['samples_pending_approval'] }}</div><div class="merch-stat-lbl">Samples Pending</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#eef2f9;"><i class="fa-solid fa-boxes-stacked" style="color:#2a4b7c;"></i></div>
                <div><div class="merch-stat-val" style="color:#2a4b7c;">{{ $mine['materials_not_booked'] }}</div><div class="merch-stat-lbl">Materials Not Booked</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f0f9ff;"><i class="fa-solid fa-ship" style="color:#0ea5e9;"></i></div>
                <div><div class="merch-stat-val" style="color:#0ea5e9;">{{ $mine['shipments_this_month'] }}</div><div class="merch-stat-lbl">Shipments This Month</div></div>
            </div>
        </div>
    </div>

    {{-- ── Orders by Status + Quick Links ── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">My Orders by Status</div>
                @if(collect($mine['orders_by_status'])->sum())
                    <div id="merchOrdersByStatus" style="height:230px;"></div>
                @else
                    <div class="text-muted text-center py-4" style="font-size:13px;">No orders yet</div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Quick Links</div>
                <div class="d-flex flex-column gap-2">
                    @can('merch_sales_contract.list')<a href="{{ route('merchandising-trace.sales-contracts.index') }}" class="merch-quick-btn"><i class="fa-solid fa-file-signature"></i> Sales Contracts</a>@endcan
                    @can('merch_tna.list')<a href="{{ route('merchandising-trace.tna-plans.index') }}" class="merch-quick-btn"><i class="fa-solid fa-calendar-check"></i> T&amp;A Plans</a>@endcan
                    @can('merch_production_handover.list')<a href="{{ route('merchandising-trace.production-handovers.index') }}" class="merch-quick-btn"><i class="fa-solid fa-right-left"></i> Handover to Production</a>@endcan
                    @can('merch_reports.list')<a href="{{ route('merchandising-trace.reports.index') }}" class="merch-quick-btn"><i class="fa-solid fa-file-invoice"></i> Reports</a>@endcan
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Pending Approvals</div>
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="merch-stat-val" style="color:#7c3aed;font-size:20px;">{{ $mine['samples_pending_approval'] }}</div>
                        <div class="merch-stat-lbl">Samples Pending</div>
                    </div>
                    <div class="col-6">
                        <div class="merch-stat-val" style="color:{{ $mine['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};font-size:20px;">{{ $mine['pcd_risk'] }}</div>
                        <div class="merch-stat-lbl">PCD Risk POs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Production Progress (my POs) ── --}}
    <div class="merch-chart-card mb-4">
        <div class="merch-section-title">Production Progress (my POs)</div>
        <div class="table-responsive">
            <table class="table table-sm merch-recent-table mb-0">
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
                        <tr><td colspan="6" class="text-center text-muted py-3">No production progress synced yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @can('merch_dashboard.view_all')
        <hr class="my-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0" style="font-size:17px;font-weight:700;">
                <i class="fa-solid fa-chart-line me-2" style="color:#2a4b7c;"></i> Management Overview
            </h4>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4">
                <div class="merch-stat-card">
                    <div class="merch-stat-icon" style="background:{{ $management['on_time_pcd_percent'] >= 80 ? '#ecfdf5' : '#fff1f2' }};"><i class="fa-solid fa-flag-checkered" style="color:{{ $management['on_time_pcd_percent'] >= 80 ? '#10b981' : '#f43f5e' }};"></i></div>
                    <div><div class="merch-stat-val" style="color:{{ $management['on_time_pcd_percent'] >= 80 ? '#10b981' : '#f43f5e' }};">{{ $management['on_time_pcd_percent'] }}%</div><div class="merch-stat-lbl">On-time PCD</div></div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="merch-stat-card">
                    <div class="merch-stat-icon" style="background:{{ $management['on_time_shipment_percent'] >= 80 ? '#ecfdf5' : '#fff1f2' }};"><i class="fa-solid fa-ship" style="color:{{ $management['on_time_shipment_percent'] >= 80 ? '#10b981' : '#f43f5e' }};"></i></div>
                    <div><div class="merch-stat-val" style="color:{{ $management['on_time_shipment_percent'] >= 80 ? '#10b981' : '#f43f5e' }};">{{ $management['on_time_shipment_percent'] }}%</div><div class="merch-stat-lbl">On-time Shipment</div></div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="merch-stat-card">
                    <div class="merch-stat-icon" style="background:#f5f0fb;"><i class="fa-solid fa-stopwatch" style="color:#7c3aed;"></i></div>
                    <div><div class="merch-stat-val" style="color:#7c3aed;">{{ $management['avg_sample_turnaround_days'] }}</div><div class="merch-stat-lbl">Avg Sample Turnaround (d)</div></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="merch-chart-card h-100">
                    <div class="merch-section-title">Order Book Value by Buyer</div>
                    @if(collect($management['order_book_value_by_buyer'])->sum())
                        <div id="merchOrderBookByBuyer" style="height:230px;"></div>
                    @else
                        <div class="text-muted text-center py-4" style="font-size:13px;">No data</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="merch-chart-card h-100">
                    <div class="merch-section-title">Order Book Value by Season</div>
                    @if(collect($management['order_book_value_by_season'])->sum())
                        <div id="merchOrderBookBySeason" style="height:230px;"></div>
                    @else
                        <div class="text-muted text-center py-4" style="font-size:13px;">No data</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="merch-chart-card h-100">
                    <div class="merch-section-title">Delay Reasons Pareto (top 10)</div>
                    @if(collect($management['delay_reasons_pareto'])->sum())
                        <div id="merchDelayPareto" style="height:250px;"></div>
                    @else
                        <div class="text-muted text-center py-4" style="font-size:13px;">No delays recorded</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="merch-chart-card h-100">
                    <div class="merch-section-title">PCD Failures by Responsible Department</div>
                    @if(collect($management['pcd_failures_by_dept'])->sum())
                        <div id="merchPcdFailuresByDept" style="height:250px;"></div>
                    @else
                        <div class="text-muted text-center py-4" style="font-size:13px;">No PCD failures</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="alert alert-secondary mt-3" style="font-size:13px;">
            Note: "capacity vs booked qty by month/factory" is not shown here — no factory capacity-planning data source exists anywhere else in this build to draw it from.
        </div>
    @endcan
</div>

@push('js')
<script>
(function() {
    var ordersByStatus = {!! json_encode($mine['orders_by_status']) !!};
    @isset($management)
    var buyerBook = {!! json_encode($management['order_book_value_by_buyer']) !!};
    var seasonBook = {!! json_encode($management['order_book_value_by_season']) !!};
    var delayPareto = {!! json_encode($management['delay_reasons_pareto']) !!};
    var pcdByDept = {!! json_encode($management['pcd_failures_by_dept']) !!};
    @endisset

    function label(s) { return s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' '); }

    function initCharts() {
        var statusKeys = Object.keys(ordersByStatus);
        if (statusKeys.length) {
            new ApexCharts(document.getElementById('merchOrdersByStatus'), {
                series: statusKeys.map(function (k) { return ordersByStatus[k]; }),
                chart: { type: 'donut', height: 230 },
                labels: statusKeys.map(label),
                colors: ['#94a3b8', '#2a4b7c', '#c97a2b', '#22c55e', '#f43f5e', '#6366f1', '#0ea5e9'],
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: { enabled: true, formatter: function (val) { return Math.round(val) + '%'; } },
                plotOptions: { pie: { donut: { size: '65%' } } },
                stroke: { width: 0 },
            }).render();
        }

        @isset($management)
        function barChart(elId, dataObj, color) {
            var keys = Object.keys(dataObj);
            if (!keys.length) return;
            new ApexCharts(document.getElementById(elId), {
                series: [{ name: 'Value', data: keys.map(function (k) { return dataObj[k]; }) }],
                chart: { type: 'bar', height: 230, toolbar: { show: false } },
                colors: [color],
                plotOptions: { bar: { horizontal: true, borderRadius: 3 } },
                xaxis: { categories: keys, labels: { style: { fontSize: '11px' } } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            }).render();
        }
        barChart('merchOrderBookByBuyer', buyerBook, '#2a4b7c');
        barChart('merchOrderBookBySeason', seasonBook, '#c97a2b');
        barChart('merchDelayPareto', delayPareto, '#f43f5e');
        barChart('merchPcdFailuresByDept', pcdByDept, '#7c3aed');
        @endisset
    }

    if (typeof ApexCharts !== 'undefined') {
        initCharts();
    } else {
        var scriptTag = document.createElement('script');
        scriptTag.src = '{{ asset("admin/assets/js/apexcharts/apexcharts.min.js") }}';
        scriptTag.onload = initCharts;
        document.head.appendChild(scriptTag);
    }
})();
</script>
@endpush
@endsection
