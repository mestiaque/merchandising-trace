{{-- props: tna_milestone (optional, for edit), ordersOptions --}}
<div class="mb-3">
    <label class="form-label">Order <span class="text-danger">*</span></label>
    <select name="order_id" class="form-control merch-select2" required>
        <option value="">— Select —</option>
        @foreach($ordersOptions as $order)
            <option value="{{ $order->id }}" @selected(old('order_id', $tna_milestone->order_id ?? '') == $order->id)>{{ $order->po_number }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Milestone <span class="text-danger">*</span></label>
    <input type="text" name="milestone_name" class="form-control" list="milestoneNames" value="{{ old('milestone_name', $tna_milestone->milestone_name ?? '') }}" required>
    <datalist id="milestoneNames">
        @foreach(\ME\MerchandisingTrace\Models\TnaMilestone::MILESTONES as $name)
            <option value="{{ $name }}">
        @endforeach
    </datalist>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Planned Date <span class="text-danger">*</span></label>
        <input type="date" name="planned_date" class="form-control" value="{{ old('planned_date', optional($tna_milestone->planned_date ?? null)->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Actual Date</label>
        <input type="date" name="actual_date" class="form-control" value="{{ old('actual_date', optional($tna_milestone->actual_date ?? null)->format('Y-m-d')) }}">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Status <span class="text-danger">*</span></label>
    <select name="status" class="form-control" required>
        <option value="pending" @selected(old('status', $tna_milestone->status ?? 'pending') === 'pending')>Pending</option>
        <option value="completed" @selected(old('status', $tna_milestone->status ?? 'pending') === 'completed')>Completed</option>
    </select>
</div>
<div class="form-check form-switch mb-3">
    <input type="hidden" name="is_escalated" value="0">
    <input type="checkbox" name="is_escalated" value="1" class="form-check-input" id="tnaEscalated{{ $tna_milestone->id ?? 'new' }}" @checked(old('is_escalated', $tna_milestone->is_escalated ?? false))>
    <label class="form-check-label" for="tnaEscalated{{ $tna_milestone->id ?? 'new' }}">Escalated</label>
</div>
<div class="mb-3">
    <label class="form-label">Remarks</label>
    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $tna_milestone->remarks ?? '') }}</textarea>
</div>
