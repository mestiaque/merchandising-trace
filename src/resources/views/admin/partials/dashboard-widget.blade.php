@php
    try {
        $merchStats = \ME\MerchandisingTrace\Http\Controllers\DashboardController::stats();
    } catch (\Throwable $e) {
        $merchStats = null;
    }
@endphp
@if($merchStats)
@php
    $s            = $merchStats;
    $trendLabels  = collect($s['orderTrend'])->pluck('label')->toJson();
    $trendCounts  = collect($s['orderTrend'])->pluck('count')->toJson();
    $statusLabels = collect($s['statusBreakdown'])->keys()->map(fn ($k) => ucfirst(str_replace('_', ' ', $k)))->toJson();
    $statusValues = collect($s['statusBreakdown'])->values()->toJson();
    $widgetId     = 'merch_widget_' . uniqid();
@endphp

<style>
.merch-stat-card-link { display: block; text-decoration: none; color: inherit; height: 100%; }
.merch-stat-card { background: #fff; border-radius: 12px; padding: 20px 18px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); border: none; transition: transform .2s, box-shadow .2s; height: 100%; }
.merch-stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.11); }
.merch-stat-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
.merch-stat-val { font-size: 24px; font-weight: 700; line-height: 1; margin-bottom: 3px; }
.merch-stat-lbl { font-size: 12px; color: #888; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }
.merch-section-title { font-size: 13px; font-weight: 700; color: #444; text-transform: uppercase; letter-spacing: 1px; border-left: 3px solid #7c3aed; padding-left: 10px; margin-bottom: 16px; }
.merch-chart-card { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 12px rgba(0,0,0,.07); height: 100%; }
.merch-quick-btn { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; background: #f5f3ff; border: 1px solid #ede9fe; color: #444; font-size: 13px; font-weight: 500; text-decoration: none; transition: all .2s; }
.merch-quick-btn:hover { background: #7c3aed; color: #fff; border-color: #7c3aed; }
.merch-quick-btn i { width: 20px; text-align: center; }
</style>

{{-- ── Section Header ── --}}
<div class="d-flex align-items-center justify-content-between mb-3 mt-1">
    <h4 class="mb-0" style="font-size:17px;font-weight:700;">
        <i class="fa-solid fa-shirt me-2" style="color:#7c3aed;"></i> Merchandising Overview
    </h4>
    @if(\Illuminate\Support\Facades\Route::has('merchandising-trace.dashboard'))
        <a href="{{ route('merchandising-trace.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="font-size:12px;">
            <i class="fa-solid fa-gauge me-1"></i> Merchandising Dashboard
        </a>
    @endif
</div>

{{-- ── Stat Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg">
        @if(\Illuminate\Support\Facades\Route::has('merchandising-trace.orders.index'))<a href="{{ route('merchandising-trace.orders.index') }}" class="merch-stat-card-link">@endif
        <div class="merch-stat-card">
            <div class="merch-stat-icon" style="background:#f5f3ff;"><i class="fa-solid fa-file-invoice" style="color:#7c3aed;"></i></div>
            <div>
                <div class="merch-stat-val" style="color:#7c3aed;">{{ number_format($s['totalOrders']) }}</div>
                <div class="merch-stat-lbl">Total Orders</div>
            </div>
        </div>
        @if(\Illuminate\Support\Facades\Route::has('merchandising-trace.orders.index'))</a>@endif
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="merch-stat-card">
            <div class="merch-stat-icon" style="background:#eef2ff;"><i class="fa-solid fa-play" style="color:#6366f1;"></i></div>
            <div>
                <div class="merch-stat-val" style="color:#6366f1;">{{ number_format($s['runningOrders']) }}</div>
                <div class="merch-stat-lbl">In Production</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        @if(\Illuminate\Support\Facades\Route::has('merchandising-trace.samples.index'))<a href="{{ route('merchandising-trace.samples.index') }}" class="merch-stat-card-link">@endif
        <div class="merch-stat-card">
            <div class="merch-stat-icon" style="background:#fffbeb;"><i class="fa-solid fa-vial" style="color:#f59e0b;"></i></div>
            <div>
                <div class="merch-stat-val" style="color:#f59e0b;">{{ number_format($s['pendingSamples']) }}</div>
                <div class="merch-stat-lbl">Pending Samples</div>
            </div>
        </div>
        @if(\Illuminate\Support\Facades\Route::has('merchandising-trace.samples.index'))</a>@endif
    </div>
    <div class="col-6 col-md-4 col-lg">
        @if(\Illuminate\Support\Facades\Route::has('merchandising-trace.tna-milestones.index'))<a href="{{ route('merchandising-trace.tna-milestones.index', ['delayed_only' => 1]) }}" class="merch-stat-card-link">@endif
        <div class="merch-stat-card">
            <div class="merch-stat-icon" style="background:#fff1f2;"><i class="fa-solid fa-triangle-exclamation" style="color:#f43f5e;"></i></div>
            <div>
                <div class="merch-stat-val" style="color:#f43f5e;">{{ number_format($s['delayedMilestones']) }}</div>
                <div class="merch-stat-lbl">Delayed TNA</div>
            </div>
        </div>
        @if(\Illuminate\Support\Facades\Route::has('merchandising-trace.tna-milestones.index'))</a>@endif
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="merch-stat-card">
            <div class="merch-stat-icon" style="background:#ecfdf5;"><i class="fa-solid fa-percent" style="color:#10b981;"></i></div>
            <div>
                <div class="merch-stat-val" style="color:#10b981;">{{ $s['avgMargin'] }}%</div>
                <div class="merch-stat-lbl">Avg Margin</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="merch-stat-card">
            <div class="merch-stat-icon" style="background:#f0f9ff;"><i class="fa-solid fa-handshake" style="color:#0ea5e9;"></i></div>
            <div>
                <div class="merch-stat-val" style="color:#0ea5e9;">{{ number_format($s['totalBuyers']) }}</div>
                <div class="merch-stat-lbl">Active Buyers</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Trend + Status ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="merch-chart-card">
            <div class="merch-section-title">Orders — Last 6 Months</div>
            <div id="{{ $widgetId }}_trend" style="height:220px;"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="merch-chart-card h-100">
            <div class="merch-section-title">Orders by Status</div>
            <div id="{{ $widgetId }}_status" style="height:220px;"></div>
        </div>
    </div>
</div>

{{-- ── Top Buyers + Quick Links ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="merch-chart-card h-100">
            <div class="merch-section-title">Top Buyers (by Qty)</div>
            @if($s['topBuyers']->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach($s['topBuyers'] as $row)
                        <div class="d-flex align-items-center justify-content-between" style="font-size:13px;">
                            <span>{{ $row->buyer->name ?? '-' }}</span>
                            <span class="text-muted">{{ number_format($row->total_qty) }} ({{ $row->order_count }} orders)</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted text-center py-4" style="font-size:13px;">No data yet</div>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="merch-chart-card h-100">
            <div class="merch-section-title">Quick Links</div>
            <div class="d-flex flex-column gap-2">
                @can('merch_order.list')<a href="{{ route('merchandising-trace.orders.index') }}" class="merch-quick-btn"><i class="fa-solid fa-file-invoice"></i> Orders</a>@endcan
                @can('merch_bom.list')<a href="{{ route('merchandising-trace.boms.index') }}" class="merch-quick-btn"><i class="fa-solid fa-list-check"></i> BOM</a>@endcan
                @can('merch_sample.list')<a href="{{ route('merchandising-trace.samples.index') }}" class="merch-quick-btn"><i class="fa-solid fa-vial"></i> Samples</a>@endcan
                @can('merch_report.view')<a href="{{ route('merchandising-trace.reports.index') }}" class="merch-quick-btn"><i class="fa-solid fa-chart-line"></i> Reports</a>@endcan
            </div>
        </div>
    </div>
</div>

{{-- ── ApexCharts ── --}}
@push('js')
<script>
(function() {
    var trendLabels = {!! $trendLabels !!};
    var trendCounts = {!! $trendCounts !!};
    var statusLabels = {!! $statusLabels !!};
    var statusValues = {!! $statusValues !!};

    function initCharts() {
        new ApexCharts(document.getElementById('{{ $widgetId }}_trend'), {
            series: [{ name: 'Orders', data: trendCounts }],
            chart: { type: 'bar', height: 220, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            colors: ['#7c3aed'],
            xaxis: { categories: trendLabels, labels: { style: { fontSize: '11px' } } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
        }).render();

        new ApexCharts(document.getElementById('{{ $widgetId }}_status'), {
            series: statusValues,
            chart: { type: 'donut', height: 220 },
            labels: statusLabels,
            colors: ['#94a3b8', '#0ea5e9', '#f59e0b', '#22c55e', '#f43f5e'],
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
