<div class="row">
<div class="col-md-3 mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $size->name ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Sort Order</label>
    <input type="number" min="0" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $size->sort_order ?? 0) }}">
</div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="sizeActive{{ $size->id ?? 'new' }}" @checked(old('is_active', $size->is_active ?? true))>
    <label class="custom-control-label" for="sizeActive{{ $size->id ?? 'new' }}">Active</label>
</div>
</div></div>
