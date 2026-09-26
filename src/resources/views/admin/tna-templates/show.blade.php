@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle($tnaTemplate->name) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $tnaTemplate->name }} @if($tnaTemplate->is_default)<span class="badge badge-success">Default</span>@endif</h4>
            <div>
                @can('merch_tna.add')
                    <button type="button" class="btn btn-outline-secondary btn-sm mr-1" data-toggle="modal" data-target="#addTaskModal"><i class="fa-solid fa-plus"></i> Add Task</button>
                @endcan
                <a href="{{ route('merchandising-trace.tna-templates.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <strong>Anchor:</strong> {{ ucfirst($tnaTemplate->anchor) }} · <strong>Code:</strong> {{ $tnaTemplate->code }}
        </div>
    </div>

    @foreach($tnaTemplate->tasks->groupBy('group_name') as $group => $tasks)
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ $group }}</h6></div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead><tr><th>Task</th><th>Type</th><th>Offset (days)</th><th>Dept</th><th>Mandatory</th><th>Blocks PCD</th><th>Auto Source</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>{{ $task->task_name }}</td>
                                <td>{{ $task->value_type }}</td>
                                <td>{{ $task->offset_days }}</td>
                                <td>{{ $task->department->name ?? '-' }}</td>
                                <td>{{ $task->is_mandatory ? 'Yes' : '-' }}</td>
                                <td>{{ $task->blocks_pcd ? 'Yes' : '-' }}</td>
                                <td>{{ $task->auto_source }}</td>
                                <td class="text-right">
                                    @can('merch_tna.edit')
                                        <button type="button" class="btn-custom danger" data-toggle="modal" data-target="#deleteTaskModal{{ $task->id }}"><i class="fa-solid fa-trash"></i></button>
                                        <div class="modal fade" id="deleteTaskModal{{ $task->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog"><div class="modal-content">
                                                <form method="POST" action="{{ route('merchandising-trace.tna-templates.tasks.destroy', [$tnaTemplate, $task]) }}">
                                                    @csrf @method('DELETE')
                                                    <div class="modal-header"><h5 class="modal-title">Remove Task</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                                                    <div class="modal-body">Remove "{{ $task->task_name }}" from this template?</div>
                                                    <div class="modal-footer"><button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger btn-sm">Remove</button></div>
                                                </form>
                                            </div></div>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>

@can('merch_tna.add')
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.tna-templates.tasks.store', $tnaTemplate) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Task</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
<div class="col-md-3 mb-3"><label class="form-label">Group Name</label><input type="text" name="group_name" class="form-control form-control-sm" required placeholder="e.g. Sample Status"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Task Code</label><input type="text" name="task_code" class="form-control form-control-sm" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Task Name</label><input type="text" name="task_name" class="form-control form-control-sm" required></div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Value Type</label>
                            <select name="value_type" class="form-control form-control-sm">
                                @foreach(\ME\MerchandisingTrace\Models\TnaTemplateTask::VALUE_TYPES as $vt)
                                    <option value="{{ $vt }}">{{ ucfirst($vt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3"><label class="form-label">Offset Days (negative = before anchor)</label><input type="number" name="offset_days" class="form-control form-control-sm" value="0" required></div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Responsible Dept</label>
                            <select name="responsible_dept_id" class="form-control form-control-sm merch-select2">
                                <option value="">— Select —</option>
                                @foreach($departmentsOptions as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-center"><div class="form-check mb-2"><input type="checkbox" name="is_mandatory" value="1" class="form-check-input" id="taskMandatory"><label class="form-check-label" for="taskMandatory">Mandatory</label></div></div>
                        <div class="col-md-3 mb-3 d-flex align-items-center"><div class="form-check mb-2"><input type="checkbox" name="blocks_pcd" value="1" class="form-check-input" id="taskBlocksPcd"><label class="form-check-label" for="taskBlocksPcd">Blocks PCD</label></div>
</div></div>
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
@include('merchandising-trace::admin.partials.select2-init')
@endsection
