@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Shipment Plan') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header"><h4 class="mb-0">Shipment Plan — Plan vs Actual</h4></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>PO No</th>
                        <th>Style</th>
                        <th>Buyer</th>
                        <th>Planned Ship Date</th>
                        <th>Planned Qty</th>
                        <th>Actual Ship Date</th>
                        <th>Actual Qty</th>
                        <th>Short %</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php($po = $row['po'])
                        @php($p = $row['plan'])
                        <tr class="{{ $p['is_short'] ? 'table-danger' : '' }}">
                            <td>{{ $po->po_no }}</td>
                            <td>{{ $po->style->style_no ?? '-' }}</td>
                            <td>{{ $po->salesContract->buyer->name ?? '-' }}</td>
                            <td>{{ optional($p['planned_ship_date'])->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $p['planned_qty'] }}</td>
                            <td>{{ $p['actual_ship_date'] ? \Illuminate\Support\Carbon::parse($p['actual_ship_date'])->format('Y-m-d') : '-' }}</td>
                            <td>{{ $p['actual_qty'] }}</td>
                            <td>{{ $p['short_percent'] }}%</td>
                            <td>
                                @can('merch_shipment_plan.view')
                                    <a href="{{ route('merchandising-trace.shipment-plans.show', $po) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">No POs with a shipment date yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $paginator->links() }}</div>
    </div>
</div>
@endsection
