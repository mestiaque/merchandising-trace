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
            @can('merch_costing.add')
                <a href="{{ route('merchandising-trace.cost-sheets.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Cost Sheet</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search cost sheet no / style" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        @foreach(\ME\MerchandisingTrace\Models\CostSheet::STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.cost-sheets.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr><th>#</th><th>Cost Sheet No</th><th>Style</th><th>Inquiry</th><th>Buyer</th><th class="text-end">FOB / Dz</th><th class="text-end">FOB / Pc</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($costSheets as $cs)
                            <tr>
                                <td>{{ $loop->iteration + $costSheets->firstItem() - 1 }}</td>
                                <td>{{ $cs->cost_sheet_no }}</td>
                                <td>{{ $cs->styleLabel() }}</td>
                                <td>{{ $cs->inquiry->inquiry_no ?? '-' }}</td>
                                <td>{{ $cs->buyer->name ?? '-' }}</td>
                                <td class="text-end">{{ number_format((float) $cs->total_cost * 12, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $cs->total_cost, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($cs->status) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('merchandising-trace.cost-sheets.show', $cs) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                                    @can('merch_costing.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteCostSheetModal" data-action="{{ route('merchandising-trace.cost-sheets.destroy', $cs) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">No cost sheets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $costSheets->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteCostSheetModal', 'label' => 'cost sheets'])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
