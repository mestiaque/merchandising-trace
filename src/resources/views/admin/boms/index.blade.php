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
            <div>
                <a href="{{ route('merchandising-trace.boms.print-list', request()->query()) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
            @can('merch_bom.add')
                <a href="{{ route('merchandising-trace.boms.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Add BOM
                </a>
            @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search style" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.boms.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Style</th><th>Buyer</th><th>Version</th><th>Status</th><th>Lines</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $bom)
                            <tr>
                                <td>{{ $loop->iteration + $boms->firstItem() - 1 }}</td>
                                <td>{{ $bom->style->style_no ?? '-' }} — {{ $bom->style->name ?? '' }}</td>
                                <td>{{ $bom->style->buyer->name ?? '-' }}</td>
                                <td>v{{ $bom->version }}</td>
                                <td>
                                    @php($statusColors = ['draft' => 'secondary', 'active' => 'success', 'superseded' => 'dark'])
                                    <span class="badge p-1 text-white bg-{{ $statusColors[$bom->status] ?? 'secondary' }}">{{ ucfirst($bom->status) }}</span>
                                </td>
                                <td>{{ $bom->items_count }}</td>
                                <td class="text-end">
                                    @can('merch_bom.view')
                                        <a href="{{ route('merchandising-trace.boms.show', $bom) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                                    @endcan
                                    @can('merch_bom.edit')
                                        <a href="{{ route('merchandising-trace.boms.edit', $bom) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('merch_bom.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-delete data-confirm-label="BOM"
                                            data-action="{{ route('merchandising-trace.boms.destroy', $bom) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No BOMs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $boms->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.sweetalert-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
