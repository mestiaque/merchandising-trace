@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Material Booking') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Material Booking</h5>
            <div>
                <a href="{{ route('merchandising-trace.material-bookings.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
            @can('merch_material_booking.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createMaterialBookingModal">
                    <i class="fa-solid fa-plus"></i> Add Booking
                </button>
            @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="order_id" class="form-control merch-select2">
                        <option value="">All Orders</option>
                        @foreach($ordersOptions as $order)
                            <option value="{{ $order->id }}" @selected(request('order_id') == $order->id)>{{ $order->po_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="material_type" class="form-control merch-select2">
                        <option value="">All Types</option>
                        @foreach(\ME\MerchandisingTrace\Models\MaterialBooking::MATERIAL_TYPES as $type)
                            <option value="{{ $type }}" @selected(request('material_type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        <option value="booked" @selected(request('status') === 'booked')>Booked</option>
                        <option value="received" @selected(request('status') === 'received')>Received</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.material-bookings.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Booking No</th><th>Order</th><th>Type</th><th>Material</th><th>Qty</th><th>Supplier</th><th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($material_bookings as $material_booking)
                            <tr>
                                <td>{{ $loop->iteration + $material_bookings->firstItem() - 1 }}</td>
                                <td>{{ $material_booking->booking_number }}</td>
                                <td>{{ $material_booking->order->po_number ?? '-' }}</td>
                                <td>{{ ucfirst($material_booking->material_type) }}</td>
                                <td>{{ $material_booking->material_name }}</td>
                                <td>{{ $material_booking->qty }} {{ $material_booking->unit->short_name ?? '' }}</td>
                                <td>{{ $material_booking->supplier->name ?? '-' }}</td>
                                <td>
                                    @php($statusColors = ['booked' => 'warning', 'received' => 'success', 'cancelled' => 'danger'])
                                    <span class="badge p-1 text-white bg-{{ $statusColors[$material_booking->status] ?? 'secondary' }}">{{ ucfirst($material_booking->status) }}</span>
                                </td>
                                <td class="text-end">
                                    @can('merch_material_booking.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editMaterialBookingModal{{ $material_booking->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_material_booking.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteMaterialBookingModal" data-action="{{ route('merchandising-trace.material-bookings.destroy', $material_booking) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_material_booking.edit')
                                <div class="modal fade" id="editMaterialBookingModal{{ $material_booking->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.material-bookings.update', $material_booking) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Booking</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.material-bookings.partials.fields', ['material_booking' => $material_booking])
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No material bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $material_bookings->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_material_booking.add')
    <div class="modal fade" id="createMaterialBookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.material-bookings.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Material Booking</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('merchandising-trace::admin.material-bookings.partials.fields')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteMaterialBookingModal', 'label' => 'booking'])
@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
