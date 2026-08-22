<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $sampleType->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $sampleType->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Sequence</label>
    <input type="number" min="0" name="sequence" class="form-control" value="{{ old('sequence', $sampleType->sequence ?? 0) }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="sampleTypeActive" @checked(old('is_active', $sampleType->is_active ?? true))>
    <label class="form-check-label" for="sampleTypeActive">Active</label>
</div>
