@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('T&A Templates') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">T&amp;A Templates</h4>
            @can('merch_tna.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createTemplateModal"><i class="fa-solid fa-plus"></i> New Template</button>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead><tr><th>#</th><th>Name</th><th>Code</th><th>Buyer</th><th>Product Type</th><th>Anchor</th><th>Tasks</th><th>Default</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($templates as $t)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $t->name }}</td>
                            <td>{{ $t->code }}</td>
                            <td>{{ $t->buyer->name ?? 'All' }}</td>
                            <td>{{ $t->productType->name ?? 'All' }}</td>
                            <td>{{ ucfirst($t->anchor) }}</td>
                            <td>{{ $t->tasks_count }}</td>
                            <td>{{ $t->is_default ? 'Yes' : '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('merchandising-trace.tna-templates.show', $t) }}" class="btn-custom success"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">No templates found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@can('merch_tna.add')
    <div class="modal fade" id="createTemplateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.tna-templates.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">New Template</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
<div class="col-md-3 mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control form-control-sm" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control form-control-sm" required></div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Anchor</label>
                            <select name="anchor" class="form-control form-control-sm">
                                <option value="pcd">PCD</option>
                                <option value="shipment">Shipment</option>
                                <option value="order_confirm">Order Confirm</option>
                            </select>
                        </div>
</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
