@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Sales Contracts') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')
    @include('merchandising-trace::admin.partials.stat-card-styles')

    <div class="row mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#eef2f9;"><i class="fa-solid fa-file-signature" style="color:#2a4b7c;"></i></div>
                <div><div class="merch-stat-val" style="color:#2a4b7c;">{{ $stats['total'] }}</div><div class="merch-stat-lbl">Total</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f3f4f6;"><i class="fa-solid fa-pen" style="color:#6b7280;"></i></div>
                <div><div class="merch-stat-val" style="color:#6b7280;">{{ $stats['draft'] }}</div><div class="merch-stat-lbl">Draft</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#ecfdf5;"><i class="fa-solid fa-check" style="color:#10b981;"></i></div>
                <div><div class="merch-stat-val" style="color:#10b981;">{{ $stats['confirmed'] }}</div><div class="merch-stat-lbl">Confirmed</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f0f9ff;"><i class="fa-solid fa-industry" style="color:#0ea5e9;"></i></div>
                <div><div class="merch-stat-val" style="color:#0ea5e9;">{{ $stats['in_production'] }}</div><div class="merch-stat-lbl">In Production</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f5f0fb;"><i class="fa-solid fa-box-archive" style="color:#7c3aed;"></i></div>
                <div><div class="merch-stat-val" style="color:#7c3aed;">{{ $stats['closed'] }}</div><div class="merch-stat-lbl">Closed</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#fdf3e8;"><i class="fa-solid fa-sack-dollar" style="color:#b45309;"></i></div>
                <div><div class="merch-stat-val" style="color:#b45309;font-size:18px;">{{ number_format($stats['total_value'], 0) }}</div><div class="merch-stat-lbl">Total Value</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Sales Contracts</h4>
            @can('merch_sales_contract.add')
                <a href="{{ route('merchandising-trace.sales-contracts.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Sales Contract</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search contract no" value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control form-control-sm merch-select2">
                        <option value="">All Status</option>
                        @foreach(\ME\MerchandisingTrace\Models\SalesContract::STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <a href="{{ route('merchandising-trace.sales-contracts.index') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr><th>#</th><th>Contract No</th><th>Buyer</th><th>Contract Date</th><th>Total Qty</th><th>Total Value</th><th>Status</th><th class="text-right">Actions</th></tr>
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
                                <td><span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $sc->status)) }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('merchandising-trace.sales-contracts.show', $sc) }}" class="btn-custom success"><i class="fa-solid fa-eye"></i></a>
                                    @can('merch_sales_contract.delete')
                                        <button type="button" class="btn-custom danger" data-toggle="modal"
                                            data-target="#deleteSalesContractModal" data-action="{{ route('merchandising-trace.sales-contracts.destroy', $sc) }}"><i class="fa-solid fa-trash"></i></button>
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
