@php
    try {
        $mtwStats = \ME\MerchandisingTrace\Http\Controllers\DashboardController::stats();
    } catch (\Throwable $e) {
        $mtwStats = null;
    }
@endphp
@if($mtwStats)
@php
    $s            = $mtwStats;
    $trendDates   = collect($s['tna_trend_30d'])->keys()->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('d M'))->toJson();
    $trendPlanned = collect($s['tna_trend_30d'])->pluck('planned')->toJson();
    $trendDone    = collect($s['tna_trend_30d'])->pluck('completed')->toJson();
    $wipLabels    = collect($s['wip_by_stage'])->keys()->toJson();
    $wipValues    = collect($s['wip_by_stage'])->values()->toJson();
    $statusLabels = collect($s['plans_by_status'])->keys()->map(fn ($k) => ucfirst(str_replace('_', ' ', $k)))->toJson();
    $statusValues = collect($s['plans_by_status'])->values()->toJson();
    $widgetId     = 'mtw_widget_'.uniqid();
@endphp

<style>
.mtw-stat-card { background: #fff; border-radius: 12px; padding: 20px 18px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); border: none; transition: transform .2s, box-shadow .2s; height: 100%; }
.mtw-stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.11); }
.mtw-stat-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
.mtw-stat-val { font-size: 24px; font-weight: 700; line-height: 1; margin-bottom: 3px; }
.mtw-stat-lbl { font-size: 12px; color: #888; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }
.mtw-section-title { font-size: 13px; font-weight: 700; color: #444; text-transform: uppercase; letter-spacing: 1px; border-left: 3px solid #b45309; padding-left: 10px; margin-bottom: 16px; }
.mtw-chart-card { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 12px rgba(0,0,0,.07); height: 100%; }
.mtw-quick-btn { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; background: #fdf3e8; border: 1px solid #f0dcc0; color: #444; font-size: 13px; font-weight: 500; text-decoration: none; transition: all .2s; }
.mtw-quick-btn:hover { background: #b45309; color: #fff; border-color: #b45309; }
.mtw-quick-btn i { width: 20px; text-align: center; }
</style>

{{-- ── Section Header ── --}}
<div class="d-flex align-items-center justify-content-between mb-3 mt-1">
    <h4 class="mb-0" style="font-size:17px;font-weight:700;">
        <i class="fa-solid fa-shirt me-2" style="color:#b45309;"></i> Merchandising Overview
    </h4>
    @if(\Illuminate\Support\Facades\Route::has('merchandising-trace.dashboard'))
        <a href="{{ route('merchandising-trace.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="font-size:12px;">
            <i class="fa-solid fa-gauge me-1"></i> Full Dashboard
        </a>
    @endif
</div>

{{-- ── Stat Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg">
        <div class="mtw-stat-card">
            <div class="mtw-stat-icon" style="background:#eef2f9;"><i class="fa-solid fa-file-signature" style="color:#2a4b7c;"></i></div>
            <div>
                <div class="mtw-stat-val" style="color:#2a4b7c;">{{ number_format($s['active_pos']) }}</div>
                <div class="mtw-stat-lbl">Active POs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="mtw-stat-card">
            <div class="mtw-stat-icon" style="background:#f0f9ff;"><i class="fa-solid fa-calendar-day" style="color:#0ea5e9;"></i></div>
            <div>
                <div class="mtw-stat-val" style="color:#0ea5e9;">{{ number_format($s['tna_due_today']) }}</div>
                <div class="mtw-stat-lbl">T&amp;A Due Today</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="mtw-stat-card">
            <div class="mtw-stat-icon" style="background:{{ $s['pcd_risk'] > 0 ? '#fff7ed' : '#ecfdf5' }};"><i class="fa-solid fa-flag" style="color:{{ $s['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};"></i></div>
            <div>
                <div class="mtw-stat-val" style="color:{{ $s['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};">{{ number_format($s['pcd_risk']) }}</div>
                <div class="mtw-stat-lbl">PCD Risk POs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="mtw-stat-card">
            <div class="mtw-stat-icon" style="background:#f5f0fb;"><i class="fa-solid fa-vial" style="color:#7c3aed;"></i></div>
            <div>
                <div class="mtw-stat-val" style="color:#7c3aed;">{{ number_format($s['samples_pending_approval']) }}</div>
                <div class="mtw-stat-lbl">Samples Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="mtw-stat-card">
            <div class="mtw-stat-icon" style="background:#eef2f9;"><i class="fa-solid fa-boxes-stacked" style="color:#2a4b7c;"></i></div>
            <div>
                <div class="mtw-stat-val" style="color:#2a4b7c;">{{ number_format($s['materials_not_booked']) }}</div>
                <div class="mtw-stat-lbl">Materials Not Booked</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="mtw-stat-card">
            <div class="mtw-stat-icon" style="background:#f0f9ff;"><i class="fa-solid fa-ship" style="color:#0ea5e9;"></i></div>
            <div>
                <div class="mtw-stat-val" style="color:#0ea5e9;">{{ number_format($s['shipments_this_month']) }}</div>
                <div class="mtw-stat-lbl">Shipments This Month</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Trend + Pending Approvals ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="mtw-chart-card">
            <div class="mtw-section-title">T&amp;A Tasks vs Completed – Last 30 Days</div>
            <div id="{{ $widgetId }}_trend" style="height:220px;"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="mtw-chart-card h-100">
            <div class="mtw-section-title">Pending Approvals</div>
            <div class="row g-2 text-center">
                <div class="col-6">
                    <div class="mtw-stat-val" style="color:#7c3aed;font-size:20px;">{{ $s['samples_pending_approval'] }}</div>
                    <div class="mtw-stat-lbl">Samples Pending</div>
                </div>
                <div class="col-6">
                    <div class="mtw-stat-val" style="color:#2a4b7c;font-size:20px;">{{ $s['materials_not_booked'] }}</div>
                    <div class="mtw-stat-lbl">Materials Not Booked</div>
                </div>
                <div class="col-6 mt-3">
                    <div class="mtw-stat-val" style="color:{{ $s['on_time_pcd_percent'] >= 80 ? '#10b981' : '#f43f5e' }};font-size:20px;">{{ $s['on_time_pcd_percent'] }}%</div>
                    <div class="mtw-stat-lbl">On-time PCD</div>
                </div>
                <div class="col-6 mt-3">
                    <div class="mtw-stat-val" style="color:{{ $s['pcd_risk'] > 0 ? '#f59e0b' : '#10b981' }};font-size:20px;">{{ $s['pcd_risk'] }}</div>
                    <div class="mtw-stat-lbl">PCD Risk POs</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── WIP + Plan Status Donut + Top Buyers ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="mtw-chart-card h-100">
            <div class="mtw-section-title">WIP by Stage</div>
            <div id="{{ $widgetId }}_wip" style="height:230px;"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="mtw-chart-card h-100">
            <div class="mtw-section-title">Plans by Status</div>
            <div id="{{ $widgetId }}_status" style="height:230px;"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="mtw-chart-card h-100">
            <div class="mtw-section-title">Top Buyers (by Qty)</div>
            @if($s['top_buyers_by_qty']->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach($s['top_buyers_by_qty'] as $name => $qty)
                        <div class="d-flex align-items-center justify-content-between" style="font-size:13px;">
                            <span>{{ $name }}</span>
                            <span class="text-muted">{{ number_format($qty) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted text-center py-4" style="font-size:13px;">No data yet</div>
            @endif
        </div>
    </div>
</div>

{{-- ── Top Styles + Quick Links ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="mtw-chart-card h-100">
            <div class="mtw-section-title">Top Styles (by Order Qty)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Style</th><th>Style No</th><th class="text-end">Order Qty</th></tr></thead>
                    <tbody>
                        @forelse($s['top_styles_by_qty'] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['style_no'] }}</td>
                                <td class="text-end">{{ number_format($row['qty']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="mtw-chart-card h-100">
            <div class="mtw-section-title">Quick Links</div>
            <div class="d-flex flex-column gap-2">
                @can('merch_sales_contract.list')<a href="{{ route('merchandising-trace.sales-contracts.index') }}" class="mtw-quick-btn"><i class="fa-solid fa-file-signature"></i> Sales Contracts</a>@endcan
                @can('merch_tna.list')<a href="{{ route('merchandising-trace.tna-plans.index') }}" class="mtw-quick-btn"><i class="fa-solid fa-calendar-check"></i> T&amp;A Plans</a>@endcan
                @can('merch_production_handover.list')<a href="{{ route('merchandising-trace.production-handovers.index') }}" class="mtw-quick-btn"><i class="fa-solid fa-right-left"></i> Handover to Production</a>@endcan
                @can('merch_reports.list')<a href="{{ route('merchandising-trace.reports.index') }}" class="mtw-quick-btn"><i class="fa-solid fa-file-invoice"></i> Reports</a>@endcan
            </div>
        </div>
    </div>
</div>

{{-- ── ApexCharts ── --}}
@push('js')
<script>
(function() {
    var trendDates = {!! $trendDates !!};
    var trendPlanned = {!! $trendPlanned !!};
    var trendDone = {!! $trendDone !!};
    var wipLabels = {!! $wipLabels !!};
    var wipValues = {!! $wipValues !!};
    var statusLabels = {!! $statusLabels !!};
    var statusValues = {!! $statusValues !!};

    function initCharts() {
        new ApexCharts(document.getElementById('{{ $widgetId }}_trend'), {
            series: [
                { name: 'Planned', data: trendPlanned },
                { name: 'Completed', data: trendDone },
            ],
            chart: { type: 'area', height: 220, toolbar: { show: false } },
            colors: ['#c97a2b', '#22c55e'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .35, opacityTo: .05 } },
            xaxis: { categories: trendDates, labels: { rotate: -45, style: { fontSize: '10px' } }, tickAmount: 10 },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            legend: { fontSize: '12px' },
        }).render();

        new ApexCharts(document.getElementById('{{ $widgetId }}_wip'), {
            series: [{ name: 'POs', data: wipValues }],
            chart: { type: 'bar', height: 230, toolbar: { show: false } },
            colors: ['#2a4b7c'],
            plotOptions: { bar: { horizontal: true, borderRadius: 3 } },
            xaxis: { categories: wipLabels, labels: { style: { fontSize: '11px' } } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
        }).render();

        new ApexCharts(document.getElementById('{{ $widgetId }}_status'), {
            series: statusValues,
            chart: { type: 'donut', height: 230 },
            labels: statusLabels,
            colors: ['#22c55e', '#f59e0b', '#f43f5e', '#94a3b8'],
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { enabled: true, formatter: (val) => Math.round(val) + '%' },
            plotOptions: { pie: { donut: { size: '65%' } } },
            stroke: { width: 0 },
        }).render();
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
@endif
