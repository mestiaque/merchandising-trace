@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Shipment Plans') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Shipment Plans</h5>
            <div>
                <a href="{{ route('merchandising-trace.shipment-plans.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
            @can('merch_shipment_plan.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createShipmentPlanModal">
                    <i class="fa-solid fa-plus"></i> Add Plan
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
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        @foreach(['planned' => 'Planned', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'delayed' => 'Delayed'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.shipment-plans.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Plan No</th><th>Order</th><th>Buyer</th><th>Planned Date</th><th>Planned Qty</th><th>Mode</th><th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipment_plans as $shipment_plan)
                            <tr>
                                <td>{{ $loop->iteration + $shipment_plans->firstItem() - 1 }}</td>
                                <td>{{ $shipment_plan->plan_number }}</td>
                                <td>{{ $shipment_plan->order->po_number ?? '-' }}</td>
                                <td>{{ $shipment_plan->order->buyer->name ?? '-' }}</td>
                                <td>{{ optional($shipment_plan->planned_date)->format('d M Y') ?? '-' }}</td>
                                <td>{{ number_format($shipment_plan->planned_qty) }}</td>
                                <td>{{ ucfirst($shipment_plan->mode) }}</td>
                                <td>
                                    @php($statusColors = ['planned' => 'secondary', 'shipped' => 'info', 'delivered' => 'success', 'delayed' => 'danger'])
                                    <span class="badge p-1 text-white bg-{{ $statusColors[$shipment_plan->status] ?? 'secondary' }}">{{ ucfirst($shipment_plan->status) }}</span>
                                </td>
                                <td class="text-end">
                                    @can('merch_shipment_plan.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editShipmentPlanModal{{ $shipment_plan->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_shipment_plan.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteShipmentPlanModal" data-action="{{ route('merchandising-trace.shipment-plans.destroy', $shipment_plan) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_shipment_plan.edit')
                                <div class="modal fade" id="editShipmentPlanModal{{ $shipment_plan->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.shipment-plans.update', $shipment_plan) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Shipment Plan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.shipment-plans.partials.fields', ['shipment_plan' => $shipment_plan])
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
                                <td colspan="9" class="text-center text-muted">No shipment plans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $shipment_plans->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_shipment_plan.add')
    <div class="modal fade" id="createShipmentPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.shipment-plans.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Shipment Plan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('merchandising-trace::admin.shipment-plans.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteShipmentPlanModal', 'label' => 'shipment plan'])
@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
