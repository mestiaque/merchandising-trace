<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $uom->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $uom->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Decimal Places</label>
    <input type="number" min="0" max="4" name="decimal_places" class="form-control" value="{{ old('decimal_places', $uom->decimal_places ?? 2) }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="uomActive{{ $uom->id ?? 'new' }}" @checked(old('is_active', $uom->is_active ?? true))>
    <label class="form-check-label" for="uomActive{{ $uom->id ?? 'new' }}">Active</label>
</div>
