<div class="row">
<div class="col-md-3 mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control form-control-sm" value="{{ old('code', $productType->code ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $productType->name ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Category</label>
    <select name="category" class="form-control form-control-sm">
        <option value="">— Select —</option>
        @foreach(['Woven', 'Knit', 'Denim', 'Sweater'] as $cat)
            <option value="{{ $cat }}" @selected(old('category', $productType->category ?? '') === $cat)>{{ $cat }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Default SMV</label>
    <input type="number" step="0.01" name="default_smv" class="form-control form-control-sm" value="{{ old('default_smv', $productType->default_smv ?? '') }}">
</div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="productTypeActive{{ $productType->id ?? 'new' }}" @checked(old('is_active', $productType->is_active ?? true))>
    <label class="custom-control-label" for="productTypeActive{{ $productType->id ?? 'new' }}">Active</label>
</div>
</div></div>
