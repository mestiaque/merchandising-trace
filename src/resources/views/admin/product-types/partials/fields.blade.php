<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $productType->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $productType->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Category</label>
    <select name="category" class="form-control">
        <option value="">— Select —</option>
        @foreach(['Woven', 'Knit', 'Denim', 'Sweater'] as $cat)
            <option value="{{ $cat }}" @selected(old('category', $productType->category ?? '') === $cat)>{{ $cat }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Default SMV</label>
    <input type="number" step="0.01" name="default_smv" class="form-control" value="{{ old('default_smv', $productType->default_smv ?? '') }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="productTypeActive{{ $productType->id ?? 'new' }}" @checked(old('is_active', $productType->is_active ?? true))>
    <label class="form-check-label" for="productTypeActive{{ $productType->id ?? 'new' }}">Active</label>
</div>
