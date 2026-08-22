@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Shipment — ' . $po->po_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $po->po_no }} — {{ $po->style->style_no ?? '-' }}</h5>
            <a href="{{ route('merchandising-trace.shipment-plans.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body row">
            <div class="col-md-3"><strong>Planned Ship Date:</strong> {{ optional($plan['planned_ship_date'])->format('Y-m-d') ?? '-' }}</div>
            <div class="col-md-3"><strong>Planned Qty:</strong> {{ $plan['planned_qty'] }}</div>
            <div class="col-md-3"><strong>Actual Qty:</strong> {{ $plan['actual_qty'] }}</div>
            <div class="col-md-3"><strong>Short %:</strong> {{ $plan['short_percent'] }}%</div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Booking / Forwarder History</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Planned Date</th><th>Forwarder</th><th>Booking No</th><th>Vessel/Flight</th><th>Short?</th><th>Reason</th></tr></thead>
                <tbody>
                    @forelse($po->shipmentBookings as $b)
                        <tr>
                            <td>{{ optional($b->planned_ship_date)->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $b->forwarder_name }}</td>
                            <td>{{ $b->booking_no }}</td>
                            <td>{{ $b->vessel_flight }}</td>
                            <td>{{ $b->is_short ? 'Yes' : 'No' }}</td>
                            <td>{{ $b->short_reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No booking recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @can('merch_shipment_plan.edit')
            <div class="card-body">
                <form method="POST" action="{{ route('merchandising-trace.shipment-plans.bookings.store', $po) }}" class="row g-2">
                    @csrf
                    <div class="col-md-2"><input type="date" name="planned_ship_date" class="form-control" placeholder="Planned Date"></div>
                    <div class="col-md-2"><input type="text" name="forwarder_name" class="form-control" placeholder="Forwarder"></div>
                    <div class="col-md-2"><input type="text" name="booking_no" class="form-control" placeholder="Booking No"></div>
                    <div class="col-md-2"><input type="text" name="vessel_flight" class="form-control" placeholder="Vessel/Flight"></div>
                    <div class="col-md-2">
                        <select name="is_short" class="form-control">
                            <option value="0">Not short</option>
                            <option value="1">Short shipment</option>
                        </select>
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Save</button></div>
                    <div class="col-md-12"><input type="text" name="short_reason" class="form-control" placeholder="Short-ship reason (required if short)"></div>
                </form>
            </div>
        @endcan
    </div>
</div>
@endsection
