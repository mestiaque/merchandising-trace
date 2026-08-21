<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $supplier->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Contact Person</label>
    <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email ?? '') }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="supplierActive" @checked(old('is_active', $supplier->is_active ?? true))>
    <label class="form-check-label" for="supplierActive">Active</label>
</div>
