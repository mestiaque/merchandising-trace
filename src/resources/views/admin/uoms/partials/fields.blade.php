<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $uom->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Short Name</label>
    <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $uom->short_name ?? '') }}" required>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="uomActive" @checked(old('is_active', $uom->is_active ?? true))>
    <label class="form-check-label" for="uomActive">Active</label>
</div>
