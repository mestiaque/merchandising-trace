<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $item->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Type</label>
    <select name="type" class="form-control" required>
        <option value="">— Select —</option>
        @foreach(['fabric' => 'Fabric', 'trim' => 'Trim', 'accessory' => 'Accessory', 'packing' => 'Packing'] as $val => $label)
            <option value="{{ $val }}" @selected(old('type', $item->type ?? '') === $val)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Category</label>
    <select name="category_id" class="form-control merch-select2">
        <option value="">— Select —</option>
        @foreach($categoriesOptions ?? [] as $c)
            <option value="{{ $c->id }}" @selected(old('category_id', $item->category_id ?? '') == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">UOM</label>
    <select name="uom_id" class="form-control merch-select2">
        <option value="">— Select —</option>
        @foreach($uomsOptions ?? [] as $u)
            <option value="{{ $u->id }}" @selected(old('uom_id', $item->uom_id ?? '') == $u->id)>{{ $u->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Default Supplier</label>
    <select name="default_supplier_id" class="form-control merch-select2">
        <option value="">— Select —</option>
        @foreach($suppliersOptions ?? [] as $s)
            <option value="{{ $s->id }}" @selected(old('default_supplier_id', $item->default_supplier_id ?? '') == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Default Price</label>
    <input type="number" step="0.0001" name="default_price" class="form-control" value="{{ old('default_price', $item->default_price ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Consumption UOM</label>
    <input type="text" name="consumption_uom" class="form-control" value="{{ old('consumption_uom', $item->consumption_uom ?? '') }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="itemActive" @checked(old('is_active', $item->is_active ?? true))>
    <label class="form-check-label" for="itemActive">Active</label>
</div>
