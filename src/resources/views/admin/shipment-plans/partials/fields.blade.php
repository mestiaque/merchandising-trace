{{-- props: shipment_plan (optional, for edit), ordersOptions --}}
<div class="mb-3">
    <label class="form-label">Order <span class="text-danger">*</span></label>
    <select name="order_id" class="form-control merch-select2" required>
        <option value="">— Select —</option>
        @foreach($ordersOptions as $order)
            <option value="{{ $order->id }}" @selected(old('order_id', $shipment_plan->order_id ?? '') == $order->id)>{{ $order->po_number }}</option>
        @endforeach
    </select>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Planned Date</label>
        <input type="date" name="planned_date" class="form-control" value="{{ old('planned_date', optional($shipment_plan->planned_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Actual Date</label>
        <input type="date" name="actual_date" class="form-control" value="{{ old('actual_date', optional($shipment_plan->actual_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Planned Qty <span class="text-danger">*</span></label>
        <input type="number" min="0" name="planned_qty" class="form-control" value="{{ old('planned_qty', $shipment_plan->planned_qty ?? 0) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Mode</label>
        <select name="mode" class="form-control">
            <option value="sea" @selected(old('mode', $shipment_plan->mode ?? 'sea') === 'sea')>Sea</option>
            <option value="air" @selected(old('mode', $shipment_plan->mode ?? 'sea') === 'air')>Air</option>
            <option value="land" @selected(old('mode', $shipment_plan->mode ?? 'sea') === 'land')>Land</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Destination Port</label>
        <input type="text" name="destination_port" class="form-control" value="{{ old('destination_port', $shipment_plan->destination_port ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
            @foreach(['planned' => 'Planned', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'delayed' => 'Delayed'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $shipment_plan->status ?? 'planned') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $shipment_plan->remarks ?? '') }}</textarea>
    </div>
</div>
