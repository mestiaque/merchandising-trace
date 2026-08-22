<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $supplier->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Type</label>
    <select name="type" class="form-control" required>
        <option value="">— Select —</option>
        @foreach(\ME\MerchandisingTrace\Models\Supplier::TYPES as $t)
            <option value="{{ $t }}" @selected(old('type', $supplier->type ?? '') === $t)>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Country</label>
    <input type="text" name="country" class="form-control" value="{{ old('country', $supplier->country ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Contact</label>
    <input type="text" name="contact" class="form-control" value="{{ old('contact', $supplier->contact ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Lead Time (days)</label>
    <input type="number" min="0" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', $supplier->lead_time_days ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Payment Term</label>
    <input type="text" name="payment_term" class="form-control" value="{{ old('payment_term', $supplier->payment_term ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Rating (1-5)</label>
    <input type="number" min="1" max="5" name="rating" class="form-control" value="{{ old('rating', $supplier->rating ?? '') }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="supplierActive{{ $supplier->id ?? 'new' }}" @checked(old('is_active', $supplier->is_active ?? true))>
    <label class="form-check-label" for="supplierActive{{ $supplier->id ?? 'new' }}">Active</label>
</div>
