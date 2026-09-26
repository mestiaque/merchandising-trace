<div class="row">
<div class="col-md-3 mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control form-control-sm" value="{{ old('code', $record->code ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $record->name ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Sequence</label>
    <input type="number" min="0" name="sequence" class="form-control form-control-sm" value="{{ old('sequence', $record->sequence ?? 0) }}">
</div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="sampleTypeActive{{ $record->id ?? 'new' }}" @checked(old('is_active', $record->is_active ?? true))>
    <label class="custom-control-label" for="sampleTypeActive{{ $record->id ?? 'new' }}">Active</label>
</div>
</div></div>
