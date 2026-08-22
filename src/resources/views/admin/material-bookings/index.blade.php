@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Material Bookings') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ $activeType === 'fabric' ? 'active' : '' }}" href="{{ route('merchandising-trace.material-bookings.index', ['type' => 'fabric']) }}">Fabric</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeType === 'trims' ? 'active' : '' }}" href="{{ route('merchandising-trace.material-bookings.index', ['type' => 'trims']) }}">Trims</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeType === 'accessory' ? 'active' : '' }}" href="{{ route('merchandising-trace.material-bookings.index', ['type' => 'accessory']) }}">Accessory</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeType === 'packing' ? 'active' : '' }}" href="{{ route('merchandising-trace.material-bookings.index', ['type' => 'packing']) }}">Packing</a>
                </li>
            </ul>
            @can('merch_material_booking.add')
                <a href="{{ route('merchandising-trace.material-bookings.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add Booking</a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>Booking No</th>
                        <th>Style</th>
                        <th>Supplier</th>
                        <th>Booking Date</th>
                        <th>Booked</th>
                        <th>Received</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->booking_no }}</td>
                            <td>{{ $booking->style->style_no ?? '-' }}</td>
                            <td>{{ $booking->supplier->name ?? '-' }}</td>
                            <td>{{ optional($booking->booking_date)->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $booking->totalBookedQty() }}</td>
                            <td>{{ $booking->totalReceivedQty() }}</td>
                            <td>{{ $booking->balanceQty() }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></td>
                            <td>
                                @can('merch_material_booking.view')
                                    <a href="{{ route('merchandising-trace.material-bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $bookings->links() }}</div>
    </div>
</div>
@endsection
