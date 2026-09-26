<div class="row">
<div class="col-md-3 mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control form-control-sm" value="{{ old('code', $uom->code ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $uom->name ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Decimal Places</label>
    <input type="number" min="0" max="4" name="decimal_places" class="form-control form-control-sm" value="{{ old('decimal_places', $uom->decimal_places ?? 2) }}">
</div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="uomActive{{ $uom->id ?? 'new' }}" @checked(old('is_active', $uom->is_active ?? true))>
    <label class="custom-control-label" for="uomActive{{ $uom->id ?? 'new' }}">Active</label>
</div>
</div></div>
