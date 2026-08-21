@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Product Types') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Types</h5>
            <div>
                <a href="{{ route('merchandising-trace.product-types.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
            @can('merch_product_type.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createProductTypeModal">
                    <i class="fa-solid fa-plus"></i> Add Product Type
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
                    <a href="{{ route('merchandising-trace.product-types.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th><th>Code</th><th>Category</th><th>Default SMV</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productTypes as $productType)
                            <tr>
                                <td>{{ $loop->iteration + $productTypes->firstItem() - 1 }}</td>
                                <td>{{ $productType->name }}</td><td>{{ $productType->code }}</td><td>{{ $productType->category }}</td><td>{{ $productType->default_smv }}</td>
                                <td>
                                    <span class="badge p-1 text-white bg-{{ $productType->is_active ? 'success' : 'secondary' }}">
                                        {{ $productType->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('merch_product_type.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editProductTypeModal{{ $productType->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_product_type.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteProductTypeModal" data-action="{{ route('merchandising-trace.product-types.destroy', $productType) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_product_type.edit')
                                <div class="modal fade" id="editProductTypeModal{{ $productType->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.product-types.update', $productType) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Product Type</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.product-types.partials.fields', ['productType' => $productType])
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
                                <td colspan="6" class="text-center text-muted">No product types found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $productTypes->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_product_type.add')
    <div class="modal fade" id="createProductTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.product-types.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Product Type</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @php($productType = null)
                        @include('merchandising-trace::admin.product-types.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteProductTypeModal', 'label' => 'product types'])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
