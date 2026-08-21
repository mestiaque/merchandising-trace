<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $size->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Sort Order</label>
    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $size->sort_order ?? '') }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="sizeActive{{ $size->id ?? 'new' }}" @checked(old('is_active', $size->is_active ?? true))>
    <label class="form-check-label" for="sizeActive{{ $size->id ?? 'new' }}">Active</label>
</div>
