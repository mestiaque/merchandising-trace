@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Sample Types') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sample Types</h5>
            @can('merch_sample_type.add')
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createSampleTypeModal">
                    <i class="fa-solid fa-plus"></i> Add Sample Type
                </button>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr><th>#</th><th>Sequence</th><th>Name</th><th>Code</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($sampleTypes as $sampleType)
                            <tr>
                                <td>{{ $loop->iteration + $sampleTypes->firstItem() - 1 }}</td>
                                <td>{{ $sampleType->sequence }}</td>
                                <td>{{ $sampleType->name }}</td>
                                <td>{{ $sampleType->code }}</td>
                                <td>
                                    <span class="badge p-1 text-white bg-{{ $sampleType->is_active ? 'success' : 'secondary' }}">
                                        {{ $sampleType->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('merch_sample_type.edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editSampleTypeModal{{ $sampleType->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endcan
                                    @can('merch_sample_type.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteSampleTypeModal" data-action="{{ route('merchandising-trace.sample-types.destroy', $sampleType) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                            @can('merch_sample_type.edit')
                                <div class="modal fade" id="editSampleTypeModal{{ $sampleType->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('merchandising-trace.sample-types.update', $sampleType) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Sample Type</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('merchandising-trace::admin.sample-types.partials.fields', ['sampleType' => $sampleType])
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
                            <tr><td colspan="6" class="text-center text-muted">No sample types found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $sampleTypes->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@can('merch_sample_type.add')
    <div class="modal fade" id="createSampleTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.sample-types.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Sample Type</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @php($sampleType = null)
                        @include('merchandising-trace::admin.sample-types.partials.fields')
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteSampleTypeModal', 'label' => 'sample types'])
@endsection
