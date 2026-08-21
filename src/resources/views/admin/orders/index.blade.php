@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Orders') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Orders</h5>
            <div>
                <a href="{{ route('merchandising-trace.orders.print-list', request()->query()) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
            @can('merch_order.add')
                <a href="{{ route('merchandising-trace.orders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Add Order
                </a>
            @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search PO number" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="buyer_id" class="form-control merch-select2">
                        <option value="">All Buyers</option>
                        @foreach($buyersOptions as $buyer)
                            <option value="{{ $buyer->id }}" @selected(request('buyer_id') == $buyer->id)>{{ $buyer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'in_production' => 'In Production', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.orders.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PO Number</th><th>Buyer</th><th>Style</th><th>Qty</th><th>Delivery Date</th><th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration + $orders->firstItem() - 1 }}</td>
                                <td>{{ $order->po_number }}</td>
                                <td>{{ $order->buyer->name ?? '-' }}</td>
                                <td>{{ $order->style->name ?? '-' }}</td>
                                <td>{{ number_format($order->order_qty) }}</td>
                                <td>{{ optional($order->delivery_date)->format('d M Y') ?? '-' }}</td>
                                <td>
                                    @php($statusColors = ['pending' => 'secondary', 'confirmed' => 'info', 'in_production' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'])
                                    <span class="badge p-1 text-white bg-{{ $statusColors[$order->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td class="text-end">
                                    @can('merch_order.view')
                                        <a href="{{ route('merchandising-trace.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                                    @endcan
                                    @can('merch_order.edit')
                                        <a href="{{ route('merchandising-trace.orders.edit', $order) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('merch_order.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-delete data-confirm-label="Order"
                                            data-action="{{ route('merchandising-trace.orders.destroy', $order) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.sweetalert-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
