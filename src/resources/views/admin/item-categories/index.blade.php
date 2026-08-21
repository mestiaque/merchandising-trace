@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Item Categories') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Item Categories</h5>
            <div>
                <a href="{{ route('merchandising-trace.item-categories.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
            @can('merch_item_category.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createItemCategoryModal">
                    <i class="fa-solid fa-plus"></i> Add Item Category
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
                    <a href="{{ route('merchandising-trace.item-categories.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th><th>Code</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itemCategories as $itemCategory)
                            <tr>
                                <td>{{ $loop->iteration + $itemCategories->firstItem() - 1 }}</td>
                                <td>{{ $itemCategory->name }}</td><td>{{ $itemCategory->code }}</td>
                                <td>
                                    <span class="badge p-1 text-white bg-{{ $itemCategory->is_active ? 'success' : 'secondary' }}">
                                        {{ $itemCategory->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('merch_item_category.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editItemCategoryModal{{ $itemCategory->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_item_category.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteItemCategoryModal" data-action="{{ route('merchandising-trace.item-categories.destroy', $itemCategory) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_item_category.edit')
                                <div class="modal fade" id="editItemCategoryModal{{ $itemCategory->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.item-categories.update', $itemCategory) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Item Category</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.item-categories.partials.fields', ['itemCategory' => $itemCategory])
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
                                <td colspan="4" class="text-center text-muted">No item categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $itemCategories->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_item_category.add')
    <div class="modal fade" id="createItemCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.item-categories.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Item Category</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @php($itemCategory = null)
                        @include('merchandising-trace::admin.item-categories.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteItemCategoryModal', 'label' => 'item categories'])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
