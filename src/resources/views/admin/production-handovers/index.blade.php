@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Handover to Production') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')
    @include('merchandising-trace::admin.partials.stat-card-styles')

    <div class="row mb-3">
        <div class="col-6 col-md-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f0f9ff;"><i class="fa-solid fa-clipboard-list" style="color:#0ea5e9;"></i></div>
                <div><div class="merch-stat-val" style="color:#0ea5e9;">{{ $stats['pending'] }}</div><div class="merch-stat-lbl">Pending Handover</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#ecfdf5;"><i class="fa-solid fa-check-double" style="color:#10b981;"></i></div>
                <div><div class="merch-stat-val" style="color:#10b981;">{{ $stats['ready'] }}</div><div class="merch-stat-lbl">Checklist All-Green</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:{{ $stats['blocked'] > 0 ? '#fff1f2' : '#f3f4f6' }};"><i class="fa-solid fa-triangle-exclamation" style="color:{{ $stats['blocked'] > 0 ? '#f43f5e' : '#6b7280' }};"></i></div>
                <div><div class="merch-stat-val" style="color:{{ $stats['blocked'] > 0 ? '#f43f5e' : '#6b7280' }};">{{ $stats['blocked'] }}</div><div class="merch-stat-lbl">Has Issues</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f5f0fb;"><i class="fa-solid fa-industry" style="color:#7c3aed;"></i></div>
                <div><div class="merch-stat-val" style="color:#7c3aed;">{{ $stats['handed_over'] }}</div><div class="merch-stat-lbl">Handed Over (all time)</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h4 class="mb-0">Handover to Production — POs ready to push</h4></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>PO No</th>
                        <th>Style</th>
                        <th>Buyer</th>
                        <th>Effective Qty</th>
                        <th>Checklist</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php($po = $row['po'])
                        @php($checks = $row['checks'])
                        <tr>
                            <td>{{ $po->po_no }}</td>
                            <td>{{ $po->style->style_no ?? '-' }}</td>
                            <td>{{ $po->salesContract->buyer->name ?? '-' }}</td>
                            <td>{{ $po->effectiveQty() }}</td>
                            <td>
                                @if($checks['all_passed'])
                                    <span class="badge badge-success">All checks passed</span>
                                @else
                                    <span class="badge badge-danger">{{ collect($checks)->except('all_passed')->reject(fn($c)=>$c['pass'])->count() }} check(s) failing</span>
                                @endif
                            </td>
                            <td>
                                @can('merch_production_handover.view')
                                    <a href="{{ route('merchandising-trace.production-handovers.show', $po) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No confirmed POs pending handover.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $paginator->links() }}</div>
    </div>
</div>
@endsection
