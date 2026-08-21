@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Documents') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Document Management</h5>
            <div>
                <span class="dt-excel-slot"></span>
            @can('merch_document.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createDocumentModal">
                    <i class="fa-solid fa-plus"></i> Add Document
                </button>
            @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="document_type" class="form-control merch-select2">
                        <option value="">All Types</option>
                        @foreach(\ME\MerchandisingTrace\Models\Document::DOCUMENT_TYPES as $val => $label)
                            <option value="{{ $val }}" @selected(request('document_type') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.documents.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th><th>Title</th><th>Buyer</th><th>Order</th><th>File</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            <tr>
                                <td>{{ $loop->iteration + $documents->firstItem() - 1 }}</td>
                                <td>{{ \ME\MerchandisingTrace\Models\Document::DOCUMENT_TYPES[$document->document_type] ?? $document->document_type }}</td>
                                <td>{{ $document->title }}</td>
                                <td>{{ $document->buyer->name ?? '-' }}</td>
                                <td>{{ $document->order->po_number ?? '-' }}</td>
                                <td><a href="{{ $document->file_path }}" target="_blank">View</a></td>
                                <td class="text-end">
                                    @can('merch_document.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#editDocumentModal{{ $document->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_document.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteDocumentModal" data-action="{{ route('merchandising-trace.documents.destroy', $document) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            @can('merch_document.edit')
                                <div class="modal fade" id="editDocumentModal{{ $document->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.documents.update', $document) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Document</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.documents.partials.fields', ['document' => $document])
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
                                <td colspan="7" class="text-center text-muted">No documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $documents->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_document.add')
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.documents.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Document</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @php($document = null)
                        @include('merchandising-trace::admin.documents.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteDocumentModal', 'label' => 'document'])
@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
