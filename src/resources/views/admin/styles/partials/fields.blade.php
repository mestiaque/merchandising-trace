{{-- props: style (optional, for edit) --}}
<div class="mb-3">
    <label class="form-label">Style No <span class="text-danger">*</span></label>
    <input type="text" name="style_no" class="form-control" value="{{ old('style_no', $style->style_no ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $style->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $style->description ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Image (path/URL)</label>
    <input type="text" name="image" class="form-control" value="{{ old('image', $style->image ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Buyer <span class="text-danger">*</span></label>
    <select name="buyer_id" class="form-control merch-select2" required>
        <option value="">-- Select Buyer --</option>
        @foreach($buyersOptions as $opt)
            <option value="{{ $opt->id }}" @selected(old('buyer_id', $style->buyer_id ?? '') == $opt->id)>{{ $opt->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Brand</label>
    <select name="brand_id" class="form-control merch-select2">
        <option value="">-- Select Brand --</option>
        @foreach($brandsOptions as $opt)
            <option value="{{ $opt->id }}" @selected(old('brand_id', $style->brand_id ?? '') == $opt->id)>{{ $opt->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Season</label>
    <select name="season_id" class="form-control merch-select2">
        <option value="">-- Select --</option>
        @foreach($seasonsOptions as $opt)
            <option value="{{ $opt->id }}" @selected(old('season_id', $style->season_id ?? '') == $opt->id)>{{ $opt->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Merchandiser</label>
    <select name="merchandiser_id" class="form-control merch-select2">
        <option value="">-- Select --</option>
        @foreach($merchandisersOptions as $opt)
            <option value="{{ $opt->id }}" @selected(old('merchandiser_id', $style->merchandiser_id ?? '') == $opt->id)>{{ $opt->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Wash Type</label>
    <select name="wash_type_id" class="form-control merch-select2">
        <option value="">-- Select --</option>
        @foreach($washTypesOptions as $opt)
            <option value="{{ $opt->id }}" @selected(old('wash_type_id', $style->wash_type_id ?? '') == $opt->id)>{{ $opt->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Product Type</label>
    <select name="product_type_id" class="form-control merch-select2">
        <option value="">-- Select --</option>
        @foreach($productTypesOptions as $opt)
            <option value="{{ $opt->id }}" @selected(old('product_type_id', $style->product_type_id ?? '') == $opt->id)>{{ $opt->name }}</option>
        @endforeach
    </select>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">SMV</label>
        <input type="number" step="0.01" min="0" name="smv" class="form-control" value="{{ old('smv', $style->smv ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Cost SMV</label>
        <input type="number" step="0.01" min="0" name="cost_smv" class="form-control" value="{{ old('cost_smv', $style->cost_smv ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Target CM</label>
        <input type="number" step="0.0001" min="0" name="target_cm" class="form-control" value="{{ old('target_cm', $style->target_cm ?? '') }}">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Fabric Description</label>
    <textarea name="fabric_description" class="form-control" rows="2">{{ old('fabric_description', $style->fabric_description ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Development Status</label>
    <select name="development_status" class="form-control">
        @foreach(['new' => 'New', 'in_development' => 'In Development', 'sample_stage' => 'Sample Stage', 'approved' => 'Approved', 'in_production' => 'In Production', 'closed' => 'Closed'] as $val => $label)
            <option value="{{ $val }}" @selected(old('development_status', $style->development_status ?? 'new') === $val)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="form-check form-switch mb-2">
    <input type="hidden" name="is_repeat" value="0">
    <input type="checkbox" name="is_repeat" value="1" class="form-check-input" id="styleRepeat{{ $style->id ?? 'new' }}"
        @checked(old('is_repeat', $style->is_repeat ?? false))>
    <label class="form-check-label" for="styleRepeat{{ $style->id ?? 'new' }}">Repeat Style</label>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="styleActive{{ $style->id ?? 'new' }}"
        @checked(old('is_active', $style->is_active ?? true))>
    <label class="form-check-label" for="styleActive{{ $style->id ?? 'new' }}">Active</label>
</div>
