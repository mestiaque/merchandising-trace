@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Production Coordination') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Production Coordination <span class="text-muted" style="font-size:12px;">(read-only — live from Production)</span></h5>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                @foreach($statusCounts as $status => $count)
                    <div class="col-6 col-md-3">
                        <div class="card h-100" style="border-radius:12px;">
                            <div class="card-body text-center">
                                <div style="font-size:22px;font-weight:700;color:#7c3aed;">{{ $count }}</div>
                                <div style="font-size:11px;color:#888;text-transform:uppercase;">{{ ucfirst($status) }} Lines</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order</th><th>Buyer</th><th>Line</th><th>Alloc Qty</th><th>Target/Day</th><th>Produced</th><th>Status</th><th>Start</th><th>End</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lineBookings as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row->po_number }}</td>
                                <td>{{ $row->buyer_name ?? '-' }}</td>
                                <td>{{ $row->line_name }}</td>
                                <td>{{ number_format($row->alloc_qty) }}</td>
                                <td>{{ number_format($row->target_qty) }}</td>
                                <td>{{ number_format($producedByJob[$row->id] ?? 0) }}</td>
                                <td>
                                    @php($statusColors = ['planned' => 'secondary', 'running' => 'warning', 'completed' => 'success', 'hold' => 'danger'])
                                    <span class="badge p-1 text-white bg-{{ $statusColors[$row->status] ?? 'secondary' }}">{{ ucfirst($row->status) }}</span>
                                </td>
                                <td>{{ $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('d M') : '-' }}</td>
                                <td>{{ $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('d M') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">No line bookings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
