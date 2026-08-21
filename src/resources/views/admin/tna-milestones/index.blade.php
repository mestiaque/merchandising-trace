@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('TNA') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">TNA (Time &amp; Action)</h5>
            <div>
                <a href="{{ route('merchandising-trace.tna-milestones.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
            @can('merch_tna.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createTnaMilestoneModal">
                    <i class="fa-solid fa-plus"></i> Add Milestone
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
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check mt-2">
                        <input type="checkbox" name="delayed_only" value="1" class="form-check-input" id="delayedOnly" @checked(request()->boolean('delayed_only')) onchange="this.form.submit()">
                        <label class="form-check-label" for="delayedOnly">Delayed only</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.tna-milestones.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order</th><th>Milestone</th><th>Planned Date</th><th>Actual Date</th><th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tna_milestones as $tna_milestone)
                            <tr class="{{ $tna_milestone->isDelayed() ? 'table-danger' : '' }}">
                                <td>{{ $loop->iteration + $tna_milestones->firstItem() - 1 }}</td>
                                <td>{{ $tna_milestone->order->po_number ?? '-' }}</td>
                                <td>{{ $tna_milestone->milestone_name }} @if($tna_milestone->is_escalated)<span class="badge bg-danger">Escalated</span>@endif</td>
                                <td>{{ $tna_milestone->planned_date->format('d M Y') }}</td>
                                <td>{{ optional($tna_milestone->actual_date)->format('d M Y') ?? '-' }}</td>
                                <td>
                                    <span class="badge p-1 text-white bg-{{ $tna_milestone->status === 'completed' ? 'success' : ($tna_milestone->isDelayed() ? 'danger' : 'secondary') }}">
                                        {{ $tna_milestone->isDelayed() ? 'Delayed' : ucfirst($tna_milestone->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('merch_tna.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editTnaMilestoneModal{{ $tna_milestone->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_tna.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteTnaMilestoneModal" data-action="{{ route('merchandising-trace.tna-milestones.destroy', $tna_milestone) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_tna.edit')
                                <div class="modal fade" id="editTnaMilestoneModal{{ $tna_milestone->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.tna-milestones.update', $tna_milestone) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Milestone</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.tna-milestones.partials.fields', ['tna_milestone' => $tna_milestone])
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
                                <td colspan="7" class="text-center text-muted">No milestones found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $tna_milestones->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_tna.add')
    <div class="modal fade" id="createTnaMilestoneModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.tna-milestones.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Milestone</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('merchandising-trace::admin.tna-milestones.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteTnaMilestoneModal', 'label' => 'milestone'])
@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
