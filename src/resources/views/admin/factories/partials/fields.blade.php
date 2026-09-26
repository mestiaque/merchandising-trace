<div class="row">
<div class="col-md-3 mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control form-control-sm" value="{{ old('code', $factory->code ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $factory->name ?? '') }}" required>
</div>
<div class="col-12 mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control form-control-sm" rows="2">{{ old('address', $factory->address ?? '') }}</textarea>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Unit Type</label>
    <input type="text" name="unit_type" class="form-control form-control-sm" value="{{ old('unit_type', $factory->unit_type ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Capacity / Month</label>
    <input type="number" name="capacity_per_month" class="form-control form-control-sm" value="{{ old('capacity_per_month', $factory->capacity_per_month ?? '') }}">
</div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch mb-2">
    <input type="hidden" name="is_own" value="0">
    <input type="checkbox" name="is_own" value="1" class="custom-control-input" id="factoryOwn{{ $factory->id ?? 'new' }}" @checked(old('is_own', $factory->is_own ?? true))>
    <label class="custom-control-label" for="factoryOwn{{ $factory->id ?? 'new' }}">Own Factory</label>
</div></div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="factoryActive{{ $factory->id ?? 'new' }}" @checked(old('is_active', $factory->is_active ?? true))>
    <label class="custom-control-label" for="factoryActive{{ $factory->id ?? 'new' }}">Active</label>
</div>
</div></div>
