<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $color->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $color->code ?? '') }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="colorActive{{ $color->id ?? 'new' }}" @checked(old('is_active', $color->is_active ?? true))>
    <label class="form-check-label" for="colorActive{{ $color->id ?? 'new' }}">Active</label>
</div>
