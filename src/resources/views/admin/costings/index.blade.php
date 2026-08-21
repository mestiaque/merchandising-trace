@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Costing') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Costing</h5>
            <div>
                <a href="{{ route('merchandising-trace.costings.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
            @can('merch_costing.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createCostingModal">
                    <i class="fa-solid fa-plus"></i> Add Costing
                </button>
            @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-2">
                    <select name="type" class="form-control merch-select2">
                        <option value="">All Types</option>
                        @foreach(\ME\MerchandisingTrace\Models\Costing::TYPES as $val => $label)
                            <option value="{{ $val }}" @selected(request('type') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.costings.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Costing No</th><th>Order</th><th>Buyer</th><th>Type</th><th>FOB Price</th><th>CM</th><th>Margin %</th><th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($costings as $costing)
                            <tr>
                                <td>{{ $loop->iteration + $costings->firstItem() - 1 }}</td>
                                <td>{{ $costing->costing_number }}</td>
                                <td>{{ $costing->order->po_number ?? '-' }}</td>
                                <td>{{ $costing->order->buyer->name ?? '-' }}</td>
                                <td>{{ \ME\MerchandisingTrace\Models\Costing::TYPES[$costing->type] ?? $costing->type }}</td>
                                <td>{{ number_format($costing->fob_price, 2) }}</td>
                                <td>{{ number_format($costing->cmAmount(), 2) }}</td>
                                <td>{{ $costing->profitMarginPercent() }}%</td>
                                <td>
                                    <span class="badge p-1 text-white bg-{{ $costing->status === 'approved' ? 'success' : 'secondary' }}">{{ ucfirst($costing->status) }}</span>
                                </td>
                                <td class="text-end">
                                    @can('merch_costing.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editCostingModal{{ $costing->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_costing.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteCostingModal" data-action="{{ route('merchandising-trace.costings.destroy', $costing) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_costing.edit')
                                <div class="modal fade" id="editCostingModal{{ $costing->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.costings.update', $costing) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Costing</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.costings.partials.fields', ['costing' => $costing])
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
                                <td colspan="10" class="text-center text-muted">No costings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $costings->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_costing.add')
    <div class="modal fade" id="createCostingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.costings.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Costing</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @php($costing = null)
                        @include('merchandising-trace::admin.costings.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteCostingModal', 'label' => 'costing'])
@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
