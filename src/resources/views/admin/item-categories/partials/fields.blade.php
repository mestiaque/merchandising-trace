<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $itemCategory->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $itemCategory->code ?? '') }}" required>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="itemCategoryActive" @checked(old('is_active', $itemCategory->is_active ?? true))>
    <label class="form-check-label" for="itemCategoryActive">Active</label>
</div>
