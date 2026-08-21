@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Sales Contracts') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Contracts</h5>
            <div>
                <a href="{{ route('merchandising-trace.sales-contracts.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
            @can('merch_sales_contract.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createSalesContractModal">
                    <i class="fa-solid fa-plus"></i> Add Sales Contract
                </button>
            @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="signed" @selected(request('status') === 'signed')>Signed</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.sales-contracts.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Contract No</th><th>Order</th><th>Buyer</th><th>Contract Date</th><th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales_contracts as $sales_contract)
                            <tr>
                                <td>{{ $loop->iteration + $sales_contracts->firstItem() - 1 }}</td>
                                <td>{{ $sales_contract->contract_number }}</td>
                                <td>{{ $sales_contract->order->po_number ?? '-' }}</td>
                                <td>{{ $sales_contract->buyer->name ?? '-' }}</td>
                                <td>{{ optional($sales_contract->contract_date)->format('d M Y') ?? '-' }}</td>
                                <td>
                                    @php($statusColors = ['draft' => 'secondary', 'signed' => 'success', 'cancelled' => 'danger'])
                                    <span class="badge p-1 text-white bg-{{ $statusColors[$sales_contract->status] ?? 'secondary' }}">{{ ucfirst($sales_contract->status) }}</span>
                                </td>
                                <td class="text-end">
                                    @can('merch_sales_contract.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editSalesContractModal{{ $sales_contract->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_sales_contract.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteSalesContractModal" data-action="{{ route('merchandising-trace.sales-contracts.destroy', $sales_contract) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_sales_contract.edit')
                                <div class="modal fade" id="editSalesContractModal{{ $sales_contract->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.sales-contracts.update', $sales_contract) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Sales Contract</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.sales-contracts.partials.fields', ['sales_contract' => $sales_contract])
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
                                <td colspan="7" class="text-center text-muted">No sales contracts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sales_contracts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_sales_contract.add')
    <div class="modal fade" id="createSalesContractModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.sales-contracts.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Sales Contract</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('merchandising-trace::admin.sales-contracts.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteSalesContractModal', 'label' => 'sales contract'])
@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
