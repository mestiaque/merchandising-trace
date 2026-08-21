{{-- props: sales_contract (optional, for edit), ordersOptions --}}
<div class="mb-3">
    <label class="form-label">Order <span class="text-danger">*</span></label>
    <select name="order_id" class="form-control merch-select2" required>
        <option value="">— Select —</option>
        @foreach($ordersOptions as $order)
            <option value="{{ $order->id }}" @selected(old('order_id', $sales_contract->order_id ?? '') == $order->id)>{{ $order->po_number }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Contract Date</label>
    <input type="date" name="contract_date" class="form-control" value="{{ old('contract_date', optional($sales_contract->contract_date ?? null)->format('Y-m-d')) }}">
</div>
<div class="mb-3">
    <label class="form-label">Terms</label>
    <textarea name="terms" class="form-control" rows="3">{{ old('terms', $sales_contract->terms ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Status <span class="text-danger">*</span></label>
    <select name="status" class="form-control" required>
        @foreach(['draft' => 'Draft', 'signed' => 'Signed', 'cancelled' => 'Cancelled'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $sales_contract->status ?? 'draft') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Remarks</label>
    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $sales_contract->remarks ?? '') }}</textarea>
</div>
