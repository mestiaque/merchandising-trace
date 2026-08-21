<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $payment_term->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $payment_term->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Days</label>
    <input type="number" name="days" class="form-control" value="{{ old('days', $payment_term->days ?? 0) }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $payment_term->description ?? '') }}</textarea>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="paymentTermActive" @checked(old('is_active', $payment_term->is_active ?? true))>
    <label class="form-check-label" for="paymentTermActive">Active</label>
</div>
