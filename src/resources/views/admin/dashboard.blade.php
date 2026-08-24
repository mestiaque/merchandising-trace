@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Merchandising Dashboard') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    @include('merchandising-trace::admin.partials.stat-card-styles')

    {{-- ── Section Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 mt-1">
        <h4 class="mb-0" style="font-size:17px;font-weight:700;">
            <i class="fa-solid fa-shirt me-2" style="color:#b45309;"></i> Merchandising Dashboard
        </h4>
    </div>

    {{-- ── My Stat Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#eef2f9;"><i class="fa-solid fa-calendar-day" style="color:#2a4b7c;"></i></div>
                <div><div class="merch-stat-val" style="color:#2a4b7c;">{{ $mine['tna_due_today'] }}</div><div class="merch-stat-lbl">T&amp;A Due Today</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:{{ $mine['tna_overdue'] > 0 ? '#fff1f2' : '#ecfdf5' }};"><i class="fa-solid fa-triangle-exclamation" style="color:{{ $mine['tna_overdue'] > 0 ? '#f43f5e' : '#10b981' }};"></i></div>
                <div><div class="merch-stat-val" style="color:{{ $mine['tna_overdue'] > 0 ? '#f43f5e' : '#10b981' }};">{{ $mine['tna_overdue'] }}</div><div class="merch-stat-lbl">T&amp;A Overdue</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:{{ $mine['pcd_risk'] > 0 ? '#fff7ed' : '#ecfdf5' }};"><i class="fa-solid fa-flag" style="color:{{ $mine['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};"></i></div>
                <div><div class="merch-stat-val" style="color:{{ $mine['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};">{{ $mine['pcd_risk'] }}</div><div class="merch-stat-lbl">PCD Risk POs</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f5f0fb;"><i class="fa-solid fa-vial" style="color:#7c3aed;"></i></div>
                <div><div class="merch-stat-val" style="color:#7c3aed;">{{ $mine['samples_pending_approval'] }}</div><div class="merch-stat-lbl">Samples Pending</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f0f9ff;"><i class="fa-solid fa-ship" style="color:#0ea5e9;"></i></div>
                <div><div class="merch-stat-val" style="color:#0ea5e9;">{{ $mine['shipments_this_month'] }}</div><div class="merch-stat-lbl">Shipments This Month</div></div>
            </div>
        </div>
    </div>

    {{-- ── T&amp;A Trend + Pending Approvals ── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">T&amp;A Tasks vs Completed — Last 30 Days</div>
                @if(collect($mine['tna_trend_30d'])->pluck('planned')->sum() || collect($mine['tna_trend_30d'])->pluck('completed')->sum())
                    <div id="merchTnaTrend" style="height:230px;"></div>
                @else
                    <div class="text-muted text-center py-4" style="font-size:13px;">No T&amp;A activity in the last 30 days</div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Pending Approvals</div>
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="merch-stat-val" style="color:#7c3aed;font-size:20px;">{{ $mine['samples_pending_approval'] }}</div>
                        <div class="merch-stat-lbl">Samples Pending</div>
                    </div>
                    <div class="col-6">
                        <div class="merch-stat-val" style="color:{{ $mine['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};font-size:20px;">{{ $mine['pcd_risk'] }}</div>
                        <div class="merch-stat-lbl">PCD Risk POs</div>
                    </div>
                    <div class="col-6">
                        <div class="merch-stat-val" style="color:#0ea5e9;font-size:20px;">{{ $mine['tna_due_this_week'] }}</div>
                        <div class="merch-stat-lbl">Due This Week</div>
                    </div>
                    <div class="col-6">
                        <div class="merch-stat-val" style="color:#2a4b7c;font-size:20px;">{{ $mine['materials_not_booked'] }}</div>
                        <div class="merch-stat-lbl">Materials Not Booked</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── WIP by Stage + Plans by Status + Shipment Trend ── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">WIP by Stage</div>
                @if(collect($mine['wip_by_stage'])->sum())
                    <div id="merchWipByStage" style="height:230px;"></div>
                @else
                    <div class="text-muted text-center py-4" style="font-size:13px;">No data yet</div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Plans by Status</div>
                @if(collect($mine['plans_by_status'])->sum())
                    <div id="merchPlansByStatus" style="height:230px;"></div>
                @else
                    <div class="text-muted text-center py-4" style="font-size:13px;">No data yet</div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Monthly Shipment Trend</div>
                @if(collect($mine['shipments_by_month'])->sum())
                    <div id="merchShipmentTrend" style="height:230px;"></div>
                @else
                    <div class="text-muted text-center py-4" style="font-size:13px;">No data yet</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Top Delay Reasons + Top Buyers + Quick Links ── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Top Delay Reasons</div>
                @if(collect($mine['top_delay_reasons'])->sum())
                    <div id="merchTopDelayReasons" style="height:230px;"></div>
                @else
                    <div class="text-muted text-center py-4" style="font-size:13px;">No delays recorded</div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Top Buyers (by Qty)</div>
                @if(collect($mine['top_buyers_by_qty'])->sum())
                    <div id="merchTopBuyers" style="height:230px;"></div>
                @else
                    <div class="text-muted text-center py-4" style="font-size:13px;">No data yet</div>
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
    </div>

    {{-- ── Top Styles + Orders at Risk ── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Top Styles (by Order Qty)</div>
                <div class="table-responsive">
                    <table class="table table-sm merch-recent-table mb-0">
                        <thead><tr><th>Style</th><th>Style No</th><th class="text-end">Order Qty</th></tr></thead>
                        <tbody>
                            @forelse($mine['top_styles_by_qty'] as $s)
                                <tr>
                                    <td>{{ $s['name'] }}</td>
                                    <td>{{ $s['style_no'] }}</td>
                                    <td class="text-end">{{ number_format($s['qty']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No data yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="merch-chart-card h-100">
                <div class="merch-section-title">Orders at Risk (Past Shipment Date)</div>
                <div class="table-responsive">
                    <table class="table table-sm merch-recent-table mb-0">
                        <thead><tr><th>Style / PO</th><th>Shipment Date</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($mine['orders_at_risk'] as $o)
                                <tr>
                                    <td>{{ $o['style_po'] }}</td>
                                    <td>{{ $o['shipment_date'] }}</td>
                                    <td><span class="badge bg-danger-subtle text-danger">{{ ucwords(str_replace('_', ' ', $o['status'])) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No orders at risk</td></tr>
                            @endforelse
                        </tbody>
                    </table>
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
    var tnaTrend = {!! json_encode($mine['tna_trend_30d']) !!};
    var wipByStage = {!! json_encode($mine['wip_by_stage']) !!};
    var plansByStatus = {!! json_encode($mine['plans_by_status']) !!};
    var shipmentsByMonth = {!! json_encode($mine['shipments_by_month']) !!};
    var topDelayReasons = {!! json_encode($mine['top_delay_reasons']) !!};
    var topBuyers = {!! json_encode($mine['top_buyers_by_qty']) !!};
    @isset($management)
    var buyerBook = {!! json_encode($management['order_book_value_by_buyer']) !!};
    var seasonBook = {!! json_encode($management['order_book_value_by_season']) !!};
    var delayPareto = {!! json_encode($management['delay_reasons_pareto']) !!};
    var pcdByDept = {!! json_encode($management['pcd_failures_by_dept']) !!};
    @endisset

    function label(s) { return s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' '); }

    function donutChart(elId, dataObj, colors) {
        var keys = Object.keys(dataObj);
        if (!keys.length) return;
        new ApexCharts(document.getElementById(elId), {
            series: keys.map(function (k) { return dataObj[k]; }),
            chart: { type: 'donut', height: 230 },
            labels: keys.map(label),
            colors: colors,
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { enabled: true, formatter: function (val) { return Math.round(val) + '%'; } },
            plotOptions: { pie: { donut: { size: '65%' } } },
            stroke: { width: 0 },
        }).render();
    }

    function barChart(elId, dataObj, color, opts) {
        opts = opts || {};
        var keys = Object.keys(dataObj);
        if (!keys.length) return;
        var horizontal = opts.horizontal !== false;
        new ApexCharts(document.getElementById(elId), {
            series: [{ name: opts.seriesName || 'Value', data: keys.map(function (k) { return dataObj[k]; }) }],
            chart: { type: 'bar', height: opts.height || 230, toolbar: { show: false } },
            colors: [color],
            plotOptions: { bar: horizontal ? { horizontal: true, borderRadius: 3 } : { borderRadius: 3, columnWidth: '55%' } },
            xaxis: { categories: keys, labels: { style: { fontSize: '11px' } } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
        }).render();
    }

    function initCharts() {
        donutChart('merchPlansByStatus', plansByStatus, ['#22c55e', '#f59e0b', '#f43f5e', '#94a3b8']);
        barChart('merchWipByStage', wipByStage, '#2a4b7c', { horizontal: false, seriesName: 'POs' });
        barChart('merchShipmentTrend', shipmentsByMonth, '#0ea5e9', { horizontal: false, seriesName: 'Shipments' });
        barChart('merchTopDelayReasons', topDelayReasons, '#f43f5e', { seriesName: 'Days Late Count' });
        barChart('merchTopBuyers', topBuyers, '#7c3aed', { seriesName: 'Qty' });

        var trendDates = Object.keys(tnaTrend);
        if (trendDates.length) {
            new ApexCharts(document.getElementById('merchTnaTrend'), {
                series: [
                    { name: 'Planned', data: trendDates.map(function (d) { return tnaTrend[d].planned; }) },
                    { name: 'Completed', data: trendDates.map(function (d) { return tnaTrend[d].completed; }) },
                ],
                chart: { type: 'line', height: 230, toolbar: { show: false } },
                colors: ['#c97a2b', '#22c55e'],
                xaxis: { categories: trendDates.map(function (d) { return d.slice(5); }), labels: { style: { fontSize: '10px' }, rotate: -45 } },
                stroke: { width: 2, curve: 'smooth' },
                dataLabels: { enabled: false },
                legend: { position: 'bottom', fontSize: '11px' },
                grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            }).render();
        }

        @isset($management)
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
