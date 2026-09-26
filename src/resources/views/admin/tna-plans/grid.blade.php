@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('T&A Grid') }}</title>
@endsection

@push('css')
<style>
    .tna-grid-wrapper { overflow-x: auto; overflow-y: visible; border: 1px solid #dee2e6; }
    table.tna-grid { border-collapse: separate; border-spacing: 0; margin-bottom: 0; width: max-content; }
    table.tna-grid th, table.tna-grid td { border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; font-size: 12.5px; vertical-align: middle; }
    table.tna-grid thead th { background: #f1f5f9; text-align: center; position: sticky; top: 0; z-index: 3; }
    table.tna-grid .tna-sticky-col { position: sticky; background: #fff; z-index: 2; }
    table.tna-grid thead .tna-sticky-col { z-index: 4; }
    table.tna-grid .tna-group-band { background: #e2e8f0; font-weight: 600; }
    .tna-color-green { background: #d1fae5 !important; }
    .tna-color-amber { background: #fef3c7 !important; }
    .tna-color-red { background: #fee2e2 !important; }
    .tna-color-grey { background: #f3f4f6 !important; color: #9ca3af; }
    .tna-color-blue { background: #dbeafe !important; }
    .tna-editable-cell { cursor: pointer; }
    .tna-editable-cell:hover { outline: 2px solid #6366f1; outline-offset: -2px; }
    .tna-editable-cell input { width: 100%; border: none; background: transparent; font-size: 12.5px; padding: 0; }
    .tna-legend { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
    .tna-legend-item { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .tna-legend-swatch { display: inline-block; width: 14px; height: 14px; border-radius: 3px; border: 1px solid rgba(0,0,0,.08); }
</style>
@endpush

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="mb-0">T&amp;A Grid</h4>
            <div class="tna-legend small text-muted">
                <span class="tna-legend-item"><span class="tna-legend-swatch tna-color-green"></span>Done on time</span>
                <span class="tna-legend-item"><span class="tna-legend-swatch tna-color-amber"></span>Due soon</span>
                <span class="tna-legend-item"><span class="tna-legend-swatch tna-color-red"></span>Overdue</span>
                <span class="tna-legend-item"><span class="tna-legend-swatch tna-color-grey"></span>N/A</span>
                <span class="tna-legend-item"><span class="tna-legend-swatch tna-color-blue"></span>Auto-filled (read-only)</span>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3 mb-2">
                    <select name="buyer_id" class="form-control form-control-sm merch-select2">
                        <option value="">All Buyers</option>
                        @foreach($buyersOptions as $b)
                            <option value="{{ $b->id }}" @selected(request('buyer_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="pcd_result" class="form-control form-control-sm merch-select2">
                        <option value="">PCD: All</option>
                        @foreach(\ME\MerchandisingTrace\Models\TnaPlan::PCD_RESULTS as $r)
                            <option value="{{ $r }}" @selected(request('pcd_result') === $r)>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="overall_status" class="form-control form-control-sm merch-select2">
                        <option value="">Status: All</option>
                        @foreach(\ME\MerchandisingTrace\Models\TnaPlan::OVERALL_STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('overall_status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-check mb-2">
                    <input type="checkbox" name="my_orders" value="1" class="form-check-input" id="myOrders" @checked(request('my_orders')) onchange="this.form.submit()">
                    <label class="form-check-label" for="myOrders">My Orders Only</label>
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <a href="{{ route('merchandising-trace.tna-plans.grid') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>

            @php
                // Fixed-width frozen leading columns; cumulative left offsets computed here so
                // sticky positioning works regardless of how many are added later.
                $frozenCols = [
                    ['label' => 'TNA No', 'width' => 100],
                    ['label' => 'Buyer', 'width' => 130],
                    ['label' => 'Style', 'width' => 110],
                    ['label' => 'PO No', 'width' => 110],
                    ['label' => 'Color', 'width' => 100],
                    ['label' => 'PO Qty', 'width' => 80],
                    ['label' => 'Ship Date', 'width' => 100],
                    ['label' => 'PCD', 'width' => 80],
                ];
                $offset = 0;
                foreach ($frozenCols as $i => $col) {
                    $frozenCols[$i]['left'] = $offset;
                    $offset += $col['width'];
                }
            @endphp

            <div class="tna-grid-wrapper" style="max-height: 75vh;">
                <table class="tna-grid">
                    <thead>
                        <tr>
                            @foreach($frozenCols as $col)
                                <th class="tna-sticky-col" rowspan="2" style="left:{{ $col['left'] }}px; width:{{ $col['width'] }}px; min-width:{{ $col['width'] }}px;">{{ $col['label'] }}</th>
                            @endforeach
                            @foreach($columnGroups as $groupName => $tasks)
                                <th class="tna-group-band" colspan="{{ $tasks->count() }}">{{ $groupName }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($columnGroups as $tasks)
                                @foreach($tasks as $col)
                                    <th style="min-width: 110px;">{{ $col->task_name }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php($pcdColors = ['pending' => 'secondary', 'pass' => 'success', 'fail' => 'danger'])
                        @forelse($plans as $plan)
                            @php($tasksByCode = $plan->tasks->keyBy('task_code'))
                            <tr>
                                <td class="tna-sticky-col" style="left:{{ $frozenCols[0]['left'] }}px;">
                                    <a href="{{ route('merchandising-trace.tna-plans.show', $plan) }}">{{ $plan->tna_no }}</a>
                                </td>
                                <td class="tna-sticky-col" style="left:{{ $frozenCols[1]['left'] }}px;">{{ $plan->salesContractPo->salesContract->buyer->name ?? '-' }}</td>
                                <td class="tna-sticky-col" style="left:{{ $frozenCols[2]['left'] }}px;">{{ $plan->salesContractPo->style->style_no ?? '-' }}</td>
                                <td class="tna-sticky-col" style="left:{{ $frozenCols[3]['left'] }}px;">{{ $plan->salesContractPo->po_no ?? '-' }}</td>
                                <td class="tna-sticky-col" style="left:{{ $frozenCols[4]['left'] }}px;">{{ $plan->salesContractPo->color->name ?? '-' }}</td>
                                <td class="tna-sticky-col text-right" style="left:{{ $frozenCols[5]['left'] }}px;">{{ $plan->salesContractPo?->effectiveQty() }}</td>
                                <td class="tna-sticky-col" style="left:{{ $frozenCols[6]['left'] }}px;">{{ $plan->salesContractPo?->effectiveShipment()?->format('d-M-y') ?? '-' }}</td>
                                <td class="tna-sticky-col" style="left:{{ $frozenCols[7]['left'] }}px;"><span class="badge px-2 badge-{{ $pcdColors[$plan->pcd_result] ?? 'secondary' }}">{{ ucfirst($plan->pcd_result) }}</span></td>

                                @foreach($columnGroups as $tasks)
                                    @foreach($tasks as $col)
                                        @php($task = $tasksByCode->get($col->task_code))
                                        @if(! $task)
                                            <td class="text-center text-muted">-</td>
                                        @else
                                            @php($color = $task->boardColor())
                                            <td class="tna-color-{{ $color }} @if(!$task->is_auto) tna-editable-cell @endif"
                                                @if(!$task->is_auto)
                                                    data-editable="1"
                                                    data-url="{{ route('merchandising-trace.tna-plans.tasks.update', [$plan, $task]) }}"
                                                    data-value-type="{{ $task->value_type }}"
                                                    data-current="{{ $task->value_type === 'date' ? $task->actual_date?->format('Y-m-d') : ($task->value_type === 'number' ? $task->value_number : $task->value_text) }}"
                                                @endif
                                            >
                                                <span class="tna-cell-display">
                                                    @if($task->value_type === 'date')
                                                        {{ $task->actual_date?->format('d-M-y') ?? '-' }}
                                                    @elseif($task->value_type === 'number')
                                                        {{ $task->value_number ?? '-' }}
                                                    @else
                                                        {{ $task->value_text ?? '-' }}
                                                    @endif
                                                </span>
                                            </td>
                                        @endif
                                    @endforeach
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($frozenCols) + $columnGroups->flatten()->count() }}" class="text-center text-muted">No T&amp;A plans found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $plans->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection

@push('js')
<script>
(function () {
    document.addEventListener('click', function (e) {
        var cell = e.target.closest('.tna-editable-cell');
        if (! cell || cell.querySelector('input')) {
            return;
        }

        var display = cell.querySelector('.tna-cell-display');
        var valueType = cell.dataset.valueType;
        var current = cell.dataset.current || '';
        var inputType = valueType === 'date' ? 'date' : (valueType === 'number' ? 'number' : 'text');

        var input = document.createElement('input');
        input.type = inputType;
        input.value = current;
        display.style.display = 'none';
        cell.appendChild(input);
        input.focus();

        function save() {
            var value = input.value;
            var payload = { status: value ? 'done' : 'pending' };
            if (valueType === 'date') payload.actual_date = value || null;
            else if (valueType === 'number') payload.value_number = value || null;
            else payload.value_text = value || null;

            fetch(cell.dataset.url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            })
                .then(function (r) { return r.json().then(function (json) { return { ok: r.ok, json: json }; }); })
                .then(function (res) {
                    if (res.ok && res.json.ok) {
                        cell.className = cell.className.replace(/tna-color-\w+/, 'tna-color-' + res.json.task.color);
                        display.textContent = res.json.task.display;
                        cell.dataset.current = value;
                    } else {
                        alert(res.json.message || 'Update failed.');
                    }
                    input.remove();
                    display.style.display = '';
                })
                .catch(function () {
                    alert('Network error — the cell was not saved.');
                    input.remove();
                    display.style.display = '';
                });
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter') { ev.preventDefault(); input.blur(); }
            if (ev.key === 'Escape') { input.removeEventListener('blur', save); input.remove(); display.style.display = ''; }
        });
    });
})();
</script>
@endpush
