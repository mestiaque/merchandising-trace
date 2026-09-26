<div class="row">
<div class="col-md-3 mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control form-control-sm" value="{{ old('code', $supplier->code ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $supplier->name ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Type</label>
    <select name="type" class="form-control form-control-sm" required>
        <option value="">— Select —</option>
        @foreach(\ME\MerchandisingTrace\Models\Supplier::TYPES as $t)
            <option value="{{ $t }}" @selected(old('type', $supplier->type ?? '') === $t)>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Country</label>
    <input type="text" name="country" class="form-control form-control-sm" value="{{ old('country', $supplier->country ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Contact</label>
    <input type="text" name="contact" class="form-control form-control-sm" value="{{ old('contact', $supplier->contact ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Lead Time (days)</label>
    <input type="number" min="0" name="lead_time_days" class="form-control form-control-sm" value="{{ old('lead_time_days', $supplier->lead_time_days ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Payment Term</label>
    <input type="text" name="payment_term" class="form-control form-control-sm" value="{{ old('payment_term', $supplier->payment_term ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Rating (1-5)</label>
    <input type="number" min="1" max="5" name="rating" class="form-control form-control-sm" value="{{ old('rating', $supplier->rating ?? '') }}">
</div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="supplierActive{{ $supplier->id ?? 'new' }}" @checked(old('is_active', $supplier->is_active ?? true))>
    <label class="custom-control-label" for="supplierActive{{ $supplier->id ?? 'new' }}">Active</label>
</div>
</div></div>
