@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('BOM') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Bill of Materials</h5>
            @can('merch_bom.add')
                <a href="{{ route('merchandising-trace.boms.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New BOM</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search BOM no" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        @foreach(\ME\MerchandisingTrace\Models\Bom::STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.boms.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr><th>#</th><th>BOM No</th><th>Style</th><th>Version</th><th>Option</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $bom)
                            <tr>
                                <td>{{ $loop->iteration + $boms->firstItem() - 1 }}</td>
                                <td>{{ $bom->bom_no }}</td>
                                <td>{{ $bom->style->style_no ?? '-' }} — {{ $bom->style->name ?? '' }}</td>
                                <td>v{{ $bom->version }}</td>
                                <td>{{ \ME\MerchandisingTrace\Models\Bom::TYPES[$bom->bom_type] ?? $bom->bom_type }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($bom->status) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('merchandising-trace.boms.show', $bom) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                                    @can('merch_bom.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteBomModal" data-action="{{ route('merchandising-trace.boms.destroy', $bom) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No BOMs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $boms->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteBomModal', 'label' => 'BOMs'])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
