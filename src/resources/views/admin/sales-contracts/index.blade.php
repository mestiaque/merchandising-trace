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
            @can('merch_sales_contract.add')
                <a href="{{ route('merchandising-trace.sales-contracts.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Sales Contract</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search contract no" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        @foreach(\ME\MerchandisingTrace\Models\SalesContract::STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
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
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr><th>#</th><th>Contract No</th><th>Buyer</th><th>Contract Date</th><th>Total Qty</th><th>Total Value</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($salesContracts as $sc)
                            <tr>
                                <td>{{ $loop->iteration + $salesContracts->firstItem() - 1 }}</td>
                                <td>{{ $sc->contract_no }}</td>
                                <td>{{ $sc->buyer->name ?? '-' }}</td>
                                <td>{{ $sc->contract_date?->format('Y-m-d') }}</td>
                                <td>{{ $sc->total_qty }}</td>
                                <td>{{ number_format($sc->total_value, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $sc->status)) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('merchandising-trace.sales-contracts.show', $sc) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                                    @can('merch_sales_contract.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteSalesContractModal" data-action="{{ route('merchandising-trace.sales-contracts.destroy', $sc) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No sales contracts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $salesContracts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteSalesContractModal', 'label' => 'sales contracts'])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
