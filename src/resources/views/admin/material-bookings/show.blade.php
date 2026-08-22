@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Booking ' . $booking->booking_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $booking->booking_no }} <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></h5>
            <a href="{{ route('merchandising-trace.material-bookings.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3"><strong>Type:</strong> {{ ucfirst($booking->type) }}</div>
                <div class="col-md-3"><strong>Style:</strong> {{ $booking->style->style_no ?? '-' }}</div>
                <div class="col-md-3"><strong>Supplier:</strong> {{ $booking->supplier->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Booked/Received/Balance:</strong> {{ $booking->totalBookedQty() }} / {{ $booking->totalReceivedQty() }} / {{ $booking->balanceQty() }}</div>
            </div>

            @can('merch_material_booking.edit')
                <form method="POST" action="{{ route('merchandising-trace.material-bookings.update-dates', $booking) }}" class="row g-2 border-top pt-3">
                    @csrf @method('PUT')
                    <div class="col-md-3"><label class="form-label">Booking Date (PI date)</label><input type="date" name="booking_date" class="form-control" value="{{ optional($booking->booking_date)->format('Y-m-d') }}"></div>
                    <div class="col-md-3"><label class="form-label">PI No</label><input type="text" name="pi_no" class="form-control" value="{{ $booking->pi_no }}"></div>
                    <div class="col-md-3"><label class="form-label">LC No</label><input type="text" name="lc_no" class="form-control" value="{{ $booking->lc_no }}"></div>
                    <div class="col-md-3"><label class="form-label">LC Date</label><input type="date" name="lc_date" class="form-control" value="{{ optional($booking->lc_date)->format('Y-m-d') }}"></div>
                    <div class="col-md-3"><label class="form-label">X-mill Date</label><input type="date" name="x_mill_date" class="form-control" value="{{ optional($booking->x_mill_date)->format('Y-m-d') }}"></div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            @foreach(\ME\MerchandisingTrace\Models\MaterialBooking::STATUSES as $s)
                                <option value="{{ $s }}" @selected($booking->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Save Dates</button></div>
                </form>
            @endcan
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Booked Items</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Item</th><th>Color</th><th>Booked</th><th>Received</th><th>Balance</th></tr></thead>
                <tbody>
                    @forelse($booking->items as $line)
                        <tr>
                            <td>{{ $line->item->name ?? '-' }}</td>
                            <td>{{ $line->color->name ?? '-' }}</td>
                            <td>{{ $line->booked_qty }}</td>
                            <td>{{ $line->receivedQty() }}</td>
                            <td>{{ $line->balanceQty() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($booking->type === 'fabric')
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Consignment Schedule (1st–4th)</h6></div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead><tr><th>#</th><th>Planned Date</th><th>Planned Qty</th><th>Actual Date</th><th>Received Qty</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($booking->consignments as $c)
                            <tr>
                                <td>{{ $c->consignment_no }}</td>
                                <td>{{ $c->planned_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $c->planned_qty }}</td>
                                <td>{{ $c->actual_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $c->received_qty }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($c->status) }}</span></td>
                                <td>
                                    @if($c->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#receiveConsignment{{ $c->id }}">Receive</button>
                                        <div class="modal fade" id="receiveConsignment{{ $c->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog"><div class="modal-content">
                                                <form method="POST" action="{{ route('merchandising-trace.material-bookings.consignments.receive', [$booking, $c]) }}">
                                                    @csrf
                                                    <div class="modal-header"><h5 class="modal-title">Receive Consignment {{ $c->consignment_no }}</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                                                    <div class="modal-body">
                                                        <div class="mb-2"><label class="form-label">Actual Date</label><input type="date" name="actual_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                                                        <div class="mb-2"><label class="form-label">Received Qty</label><input type="number" step="0.0001" name="received_qty" class="form-control" value="{{ $c->planned_qty }}" required></div>
                                                        <div class="mb-2"><label class="form-label">Challan No</label><input type="text" name="challan_no" class="form-control"></div>
                                                    </div>
                                                    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Receive</button></div>
                                                </form>
                                            </div></div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @can('merch_material_booking.edit')
                <div class="card-body">
                    <form method="POST" action="{{ route('merchandising-trace.material-bookings.consignments.store', $booking) }}" class="row g-2">
                        @csrf
                        <div class="col-md-2"><select name="consignment_no" class="form-control"><option value="1">1st</option><option value="2">2nd</option><option value="3">3rd</option><option value="4">4th</option></select></div>
                        <div class="col-md-3"><input type="date" name="planned_date" class="form-control" placeholder="Planned Date"></div>
                        <div class="col-md-3"><input type="number" step="0.0001" name="planned_qty" class="form-control" placeholder="Planned Qty"></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Schedule</button></div>
                    </form>
                </div>
            @endcan
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Receipts (item-wise)</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Date</th><th>Item</th><th>Qty</th><th>Store Ref</th></tr></thead>
                <tbody>
                    @forelse($booking->receipts as $r)
                        <tr><td>{{ $r->receive_date->format('Y-m-d') }}</td><td>{{ $r->item->name ?? '-' }}</td><td>{{ $r->qty }}</td><td>{{ $r->store_ref }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No receipts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @can('merch_material_booking.edit')
            <div class="card-body">
                <form method="POST" action="{{ route('merchandising-trace.material-bookings.receive-item', $booking) }}" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <select name="item_id" class="form-control merch-select2" required>
                            <option value="">— Select Item —</option>
                            @foreach($itemsOptions as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><input type="date" name="receive_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                    <div class="col-md-2"><input type="number" step="0.0001" name="qty" class="form-control" placeholder="Qty" required></div>
                    <div class="col-md-2"><input type="text" name="store_ref" class="form-control" placeholder="Store Ref"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Record Receipt</button></div>
                </form>
            </div>
        @endcan
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
