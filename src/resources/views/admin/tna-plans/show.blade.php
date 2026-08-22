@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('T&A ' . $tnaPlan->tna_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    @php($po = $tnaPlan->salesContractPo)
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">T&amp;A {{ $tnaPlan->tna_no }} <span class="badge bg-secondary">{{ $tnaPlan->completion_percent }}% complete</span></h5>
            <a href="{{ route('merchandising-trace.tna-plans.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <h6 class="text-muted">Style Detail (read-only, mirrored from the Sales Contract PO)</h6>
            <div class="row mb-2">
                <div class="col-md-3"><strong>Buyer:</strong> {{ $po->salesContract->buyer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Style:</strong> {{ $po->style->style_no ?? '-' }} — {{ $po->style->name ?? '' }}</div>
                <div class="col-md-3"><strong>Color:</strong> {{ $po->color->name ?? '-' }}</div>
                <div class="col-md-3"><strong>PO No:</strong> {{ $po->po_no }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>PO Qty (effective):</strong> {{ $po->effectiveQty() }}</div>
                <div class="col-md-3"><strong>PCD (effective):</strong> {{ $po->effectivePcd()?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-3"><strong>Shipment (effective):</strong> {{ $po->effectiveShipment()?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-3"><strong>Cost SMV / CM / FOB:</strong> {{ $po->cost_smv ?? '-' }} / {{ $po->cm ?? '-' }} / {{ $po->fob_foc ?? '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>Print/Emb:</strong> {{ strtoupper($po->print_emb) }}</div>
                <div class="col-md-3"><strong>Applique IH:</strong> {{ strtoupper($po->emb_applique_ih) }}</div>
                <div class="col-md-3"><strong>Studs/Stones IH:</strong> {{ strtoupper($po->studs_stones_ih) }}</div>
                <div class="col-md-3"><strong>Heat Seal IH:</strong> {{ strtoupper($po->heat_seal_ih) }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                PCD Result:
                @php($pcdColors = ['pending' => 'secondary', 'pass' => 'success', 'fail' => 'danger'])
                <span class="badge bg-{{ $pcdColors[$tnaPlan->pcd_result] ?? 'secondary' }}">{{ strtoupper($tnaPlan->pcd_result) }}</span>
            </h6>
            <div>
                <form method="POST" action="{{ route('merchandising-trace.tna-plans.evaluate-pcd', $tnaPlan) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">Evaluate PCD</button>
                </form>
                @can('merch_tna.override_pcd')
                    @if($tnaPlan->pcd_result === 'fail')
                        <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#overridePcdModal">Override</button>
                    @endif
                @endcan
            </div>
        </div>
        @if($tnaPlan->pcd_result === 'fail')
            <div class="card-body">
                <div><strong>Reason:</strong> {{ $tnaPlan->pcd_fail_reason }}</div>
                <div><strong>Responsible Dept:</strong> {{ $tnaPlan->responsibleDept->name ?? '-' }}</div>
                <div><strong>Responsible Person:</strong> {{ $tnaPlan->responsiblePerson->name ?? '-' }}</div>
            </div>
        @endif
    </div>

    @foreach($tasksByGroup as $group => $tasks)
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ $group }}</h6></div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 align-middle">
                    <thead><tr><th style="min-width:180px">Task</th><th>Plan Date</th><th>Revised</th><th>Actual</th><th style="min-width:140px">Status</th><th>Value</th><th>Dept</th><th style="min-width:220px">Update</th></tr></thead>
                    <tbody>
                        @foreach($tasks as $task)
                            @php($color = $task->boardColor())
                            @php($rowClass = ['green' => 'table-success', 'amber' => 'table-warning', 'red' => 'table-danger', 'grey' => 'table-secondary', 'blue' => 'table-info'][$color] ?? '')
                            <tr class="{{ $rowClass }}">
                                <td>{{ $task->task_name }} @if($task->blocks_pcd)<span class="badge bg-dark">blocks PCD</span>@endif</td>
                                <td>{{ $task->plan_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $task->revised_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $task->actual_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                                <td>{{ $task->value_type === 'number' ? $task->value_number : ($task->value_type === 'text' ? $task->value_text : '') }}</td>
                                <td>{{ $task->responsibleDept->name ?? '-' }}</td>
                                <td>
                                    @if($task->is_auto)
                                        <span class="text-muted small">auto-filled — read only</span>
                                    @else
                                        <form method="POST" action="{{ route('merchandising-trace.tna-plans.tasks.update', [$tnaPlan, $task]) }}" class="d-flex gap-1 flex-wrap">
                                            @csrf @method('PUT')
                                            <select name="status" class="form-control form-control-sm" style="width:110px">
                                                @foreach(\ME\MerchandisingTrace\Models\TnaTask::STATUSES as $st)
                                                    <option value="{{ $st }}" @selected($task->status === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                                                @endforeach
                                            </select>
                                            @if($task->value_type === 'date')
                                                <input type="date" name="actual_date" class="form-control form-control-sm" style="width:140px" value="{{ $task->actual_date?->format('Y-m-d') }}">
                                            @elseif($task->value_type === 'number')
                                                <input type="number" step="0.0001" name="value_number" class="form-control form-control-sm" style="width:100px" value="{{ $task->value_number }}">
                                            @else
                                                <input type="text" name="value_text" class="form-control form-control-sm" style="width:140px" value="{{ $task->value_text }}">
                                            @endif
                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>

@can('merch_tna.override_pcd')
    <div class="modal fade" id="overridePcdModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('merchandising-trace.tna-plans.override-pcd', $tnaPlan) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Override PCD to Pass</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Reason <span class="text-danger">*</span></label><textarea name="reason" class="form-control" rows="2" required></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Override</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
