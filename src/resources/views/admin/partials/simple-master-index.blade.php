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
    - approval (optional bool): the model uses Models\Concerns\RequiresApproval —
      adds an approval-status filter/column and inline Approve/Reject for
      users holding '<permPrefix>.approve' (acts on the central Approvals record)
--}}
@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle($title) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    @php($masterSlug = \Illuminate\Support\Str::after($routeBase, 'merchandising-trace.'))
    @php($hasExcel = array_key_exists($masterSlug, config('merchandising-trace-master-excel', [])))

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $title }}</h4>
            <div class="d-flex gap-2 align-items-center">
                @if($hasExcel)
                    @can($permPrefix . '.list')
                        <a href="{{ route('merchandising-trace.masters.export', $masterSlug) }}" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-excel"></i> Export</a>
                    @endcan
                    @can($permPrefix . '.add')
                        <form method="POST" action="{{ route('merchandising-trace.masters.import', $masterSlug) }}" enctype="multipart/form-data" class="d-flex gap-1">
                            @csrf
                            <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                            <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">Import</button>
                        </form>
                    @endcan
                @endif
                @can($permPrefix . '.add')
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create{{ Str::studly($modalLabel) }}Modal">
                        <i class="fa-solid fa-plus"></i> Add {{ $modalLabel }}
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search" value="{{ request('search') }}">
                </div>
                @if($approval ?? false)
                    <div class="col-md-3 mb-2">
                        <select name="approval_status" class="form-control form-control-sm">
                            <option value="">All Approval Status</option>
                            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('approval_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <a href="{{ route($routeBase . '.index') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            @foreach($columns as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                            <th>Status</th>
                            @if($approval ?? false)
                                <th>Approval</th>
                            @endif
                            <th class="text-right">Actions</th>
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
                                    <span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @if($approval ?? false)
                                    @php($approvalColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'])
                                    <td>
                                        <span class="badge badge-{{ $approvalColors[$item->approval_status] ?? 'secondary' }}"
                                            title="{{ $item->approval_status === 'rejected' ? 'Reason: ' . $item->approval_remarks : ($item->approver ? 'By ' . $item->approver->name . ' on ' . optional($item->approved_at)->format('d-M-Y') : '') }}">
                                            {{ ucfirst($item->approval_status) }}
                                        </span>
                                        @if($item->approval_status === 'rejected' && $item->approval_remarks)
                                            <div class="small text-danger">{{ \Illuminate\Support\Str::limit($item->approval_remarks, 60) }}</div>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-right">
                                    @if(($approval ?? false) && $item->isPendingApproval() && $item->pendingApproval && Route::has('admin.approvals.approve'))
                                        @can($permPrefix . '.approve')
                                            <form method="POST" action="{{ route('admin.approvals.approve', $item->pendingApproval) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-custom success" title="Approve"><i class="fa-solid fa-check"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.approvals.reject', $item->pendingApproval) }}" class="d-inline"
                                                onsubmit="var r = prompt('Reason for rejecting {{ e(addslashes($item->name)) }}:'); if (!r) { return false; } this.remarks.value = r;">
                                                @csrf
                                                <input type="hidden" name="remarks">
                                                <button type="submit" class="btn-custom danger" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                                            </form>
                                        @endcan
                                    @endif
                                    @isset($viewRouteName)
                                        @can($permPrefix . '.view')
                                            <a href="{{ route($viewRouteName, $item) }}" class="btn-custom success"><i class="fa-solid fa-eye"></i></a>
                                        @endcan
                                    @endisset
                                    @can($permPrefix . '.edit')
                                        <button type="button" class="btn-custom yellow" data-toggle="modal" data-target="#edit{{ Str::studly($modalLabel) }}Modal{{ $item->id }}"><i class="fa-solid fa-pen"></i></button>
                                    @endcan
                                    @can($permPrefix . '.delete')
                                        <button type="button" class="btn-custom danger" data-toggle="modal"
                                            data-target="#delete{{ Str::studly($modalLabel) }}Modal" data-action="{{ route($routeBase . '.destroy', $item) }}"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </td>
                            </tr>

                            @can($permPrefix . '.edit')
                                <div class="modal fade" id="edit{{ Str::studly($modalLabel) }}Modal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route($routeBase . '.update', $item) }}" enctype="multipart/form-data">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit {{ $modalLabel }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include($fieldsView, [$itemVar => $item] + ($fieldsExtra ?? []))
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + (($approval ?? false) ? 4 : 3) }}" class="text-center text-muted">No records found.</td>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route($routeBase . '.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add {{ $modalLabel }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include($fieldsView, [$itemVar => null] + ($fieldsExtra ?? []))
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

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'delete' . \Illuminate\Support\Str::studly($modalLabel) . 'Modal', 'label' => Str::plural($modalLabel)])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
