<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $factory->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $factory->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $factory->address ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Unit Type</label>
    <input type="text" name="unit_type" class="form-control" value="{{ old('unit_type', $factory->unit_type ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Capacity / Month</label>
    <input type="number" name="capacity_per_month" class="form-control" value="{{ old('capacity_per_month', $factory->capacity_per_month ?? '') }}">
</div>
<div class="form-check form-switch mb-2">
    <input type="hidden" name="is_own" value="0">
    <input type="checkbox" name="is_own" value="1" class="form-check-input" id="factoryOwn" @checked(old('is_own', $factory->is_own ?? true))>
    <label class="form-check-label" for="factoryOwn">Own Factory</label>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="factoryActive" @checked(old('is_active', $factory->is_active ?? true))>
    <label class="form-check-label" for="factoryActive">Active</label>
</div>
