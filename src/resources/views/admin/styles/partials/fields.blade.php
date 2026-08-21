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
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="styleActive{{ $style->id ?? 'new' }}"
        @checked(old('is_active', $style->is_active ?? true))>
    <label class="form-check-label" for="styleActive{{ $style->id ?? 'new' }}">Active</label>
</div>
