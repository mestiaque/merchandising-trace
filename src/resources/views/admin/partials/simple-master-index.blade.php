{{--
    Generic modal-CRUD list for simple code/name masters.
    props:
    - title: page heading
    - routeBase: e.g. 'merchandising-trace.currencies'
    - permPrefix: e.g. 'merch_currency'
    - items: paginator
    - itemVar: the singular variable name used in the fields partial (e.g. 'currency')
    - columns: ['field' => 'Label', 'relation.field' => 'Label'] — dot notation supported
    - fieldsView: blade view path for the create/edit fields partial
    - modalLabel: singular label used in headings/buttons (e.g. 'Currency')
--}}
@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle($title) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $title }}</h5>
            @can($permPrefix . '.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create{{ Str::studly($modalLabel) }}Modal">
                    <i class="fa-solid fa-plus"></i> Add {{ $modalLabel }}
                </button>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route($routeBase . '.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            @foreach($columns as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $loop->iteration + $items->firstItem() - 1 }}</td>
                                @foreach(array_keys($columns) as $field)
                                    <td>{{ data_get($item, $field) }}</td>
                                @endforeach
                                <td>
                                    <span class="badge p-1 text-white bg-{{ $item->is_active ? 'success' : 'secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @isset($viewRouteName)
                                        @can($permPrefix . '.view')
                                            <a href="{{ route($viewRouteName, $item) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                                        @endcan
                                    @endisset
                                    @can($permPrefix . '.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#edit{{ Str::studly($modalLabel) }}Modal{{ $item->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can($permPrefix . '.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#delete{{ Str::studly($modalLabel) }}Modal" data-action="{{ route($routeBase . '.destroy', $item) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can($permPrefix . '.edit')
                                <div class="modal fade" id="edit{{ Str::studly($modalLabel) }}Modal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route($routeBase . '.update', $item) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit {{ $modalLabel }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include($fieldsView, [$itemVar => $item] + ($fieldsExtra ?? []))
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
                                <td colspan="{{ count($columns) + 3 }}" class="text-center text-muted">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $items->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can($permPrefix . '.add')
    <div class="modal fade" id="create{{ Str::studly($modalLabel) }}Modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route($routeBase . '.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add {{ $modalLabel }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include($fieldsView, [$itemVar => null] + ($fieldsExtra ?? []))
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'delete' . \Illuminate\Support\Str::studly($modalLabel) . 'Modal', 'label' => Str::plural($modalLabel)])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
