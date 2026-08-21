@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Factories') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Factories</h5>
            <div>
                <a href="{{ route('merchandising-trace.factories.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
            @can('merch_factory.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createFactoryModal">
                    <i class="fa-solid fa-plus"></i> Add Factory
                </button>
            @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.factories.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th><th>Code</th><th>Unit Type</th><th>Capacity/Month</th><th>Own?</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factories as $factory)
                            <tr>
                                <td>{{ $loop->iteration + $factories->firstItem() - 1 }}</td>
                                <td>{{ $factory->name }}</td><td>{{ $factory->code }}</td><td>{{ $factory->unit_type }}</td><td>{{ $factory->capacity_per_month }}</td>
                                <td>{{ $factory->is_own ? 'Yes' : 'No' }}</td>
                                <td>
                                    <span class="badge p-1 text-white bg-{{ $factory->is_active ? 'success' : 'secondary' }}">
                                        {{ $factory->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('merch_factory.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editFactoryModal{{ $factory->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_factory.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteFactoryModal" data-action="{{ route('merchandising-trace.factories.destroy', $factory) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_factory.edit')
                                <div class="modal fade" id="editFactoryModal{{ $factory->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.factories.update', $factory) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Factory</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.factories.partials.fields', ['factory' => $factory])
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
                                <td colspan="7" class="text-center text-muted">No factories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $factories->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_factory.add')
    <div class="modal fade" id="createFactoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.factories.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Factory</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @php($factory = null)
                        @include('merchandising-trace::admin.factories.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteFactoryModal', 'label' => 'factories'])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
