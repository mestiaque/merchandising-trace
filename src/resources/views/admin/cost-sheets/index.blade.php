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
            <h4 class="mb-0">Costing</h4>
            @can('merch_costing.add')
                <a href="{{ route('merchandising-trace.cost-sheets.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Cost Sheet</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search cost sheet no / style" value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control form-control-sm merch-select2">
                        <option value="">All Status</option>
                        @foreach(\ME\MerchandisingTrace\Models\CostSheet::STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <a href="{{ route('merchandising-trace.cost-sheets.index') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr><th>#</th><th>Cost Sheet No</th><th>Style</th><th>Inquiry</th><th>Buyer</th><th class="text-right">FOB / Dz</th><th class="text-right">FOB / Pc</th><th>Status</th><th class="text-right">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($costSheets as $cs)
                            <tr>
                                <td>{{ $loop->iteration + $costSheets->firstItem() - 1 }}</td>
                                <td>{{ $cs->cost_sheet_no }}</td>
                                <td>{{ $cs->styleLabel() }}</td>
                                <td>{{ $cs->inquiry->inquiry_no ?? '-' }}</td>
                                <td>{{ $cs->buyer->name ?? '-' }}</td>
                                <td class="text-right">{{ number_format((float) $cs->total_cost * 12, 2) }}</td>
                                <td class="text-right">{{ number_format((float) $cs->total_cost, 2) }}</td>
                                <td><span class="badge badge-secondary">{{ ucfirst($cs->status) }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('merchandising-trace.cost-sheets.show', $cs) }}" class="btn-custom success"><i class="fa-solid fa-eye"></i></a>
                                    @can('merch_costing.delete')
                                        <button type="button" class="btn-custom danger" data-toggle="modal"
                                            data-target="#deleteCostSheetModal" data-action="{{ route('merchandising-trace.cost-sheets.destroy', $cs) }}"><i class="fa-solid fa-trash"></i></button>
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
