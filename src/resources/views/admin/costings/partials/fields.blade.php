{{-- props: costing (optional, for edit), ordersOptions --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Order <span class="text-danger">*</span></label>
        <select name="order_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($ordersOptions as $order)
                <option value="{{ $order->id }}" @selected(old('order_id', $costing->order_id ?? '') == $order->id)>{{ $order->po_number }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-control" required>
            @foreach(\ME\MerchandisingTrace\Models\Costing::TYPES as $val => $label)
                <option value="{{ $val }}" @selected(old('type', $costing->type ?? 'pre') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">FOB Price <span class="text-danger">*</span></label>
        <input type="number" step="0.0001" min="0" name="fob_price" class="form-control" value="{{ old('fob_price', $costing->fob_price ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Fabric Cost</label>
        <input type="number" step="0.0001" min="0" name="fabric_cost" class="form-control" value="{{ old('fabric_cost', $costing->fabric_cost ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Trim Cost</label>
        <input type="number" step="0.0001" min="0" name="trim_cost" class="form-control" value="{{ old('trim_cost', $costing->trim_cost ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Wash Cost</label>
        <input type="number" step="0.0001" min="0" name="wash_cost" class="form-control" value="{{ old('wash_cost', $costing->wash_cost ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Embroidery/Print Cost</label>
        <input type="number" step="0.0001" min="0" name="embroidery_print_cost" class="form-control" value="{{ old('embroidery_print_cost', $costing->embroidery_print_cost ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Overhead Cost</label>
        <input type="number" step="0.0001" min="0" name="overhead_cost" class="form-control" value="{{ old('overhead_cost', $costing->overhead_cost ?? 0) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
            <option value="draft" @selected(old('status', $costing->status ?? 'draft') === 'draft')>Draft</option>
            <option value="approved" @selected(old('status', $costing->status ?? 'draft') === 'approved')>Approved</option>
        </select>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $costing->remarks ?? '') }}</textarea>
    </div>
</div>
