{{-- props: sample (optional, for edit), buyersOptions, stylesOptions, sampleTypesOptions, seasonsOptions, merchandisersOptions --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Buyer <span class="text-danger">*</span></label>
        <select name="buyer_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($buyersOptions as $buyer)
                <option value="{{ $buyer->id }}" @selected(old('buyer_id', $sample->buyer_id ?? '') == $buyer->id)>{{ $buyer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Style <span class="text-danger">*</span></label>
        <select name="style_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($stylesOptions as $style)
                <option value="{{ $style->id }}" @selected(old('style_id', $sample->style_id ?? '') == $style->id)>{{ $style->style_no }} — {{ $style->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Sample Type <span class="text-danger">*</span></label>
        <select name="sample_type_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($sampleTypesOptions as $type)
                <option value="{{ $type->id }}" @selected(old('sample_type_id', $sample->sample_type_id ?? '') == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Season</label>
        <select name="season_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($seasonsOptions as $season)
                <option value="{{ $season->id }}" @selected(old('season_id', $sample->season_id ?? '') == $season->id)>{{ $season->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Merchandiser</label>
        <select name="merchandiser_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($merchandisersOptions as $user)
                <option value="{{ $user->id }}" @selected(old('merchandiser_id', $sample->merchandiser_id ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Qty <span class="text-danger">*</span></label>
        <input type="number" min="1" name="qty" class="form-control" value="{{ old('qty', $sample->qty ?? 1) }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
            @foreach(\ME\MerchandisingTrace\Models\Sample::STATUSES as $value)
                <option value="{{ $value }}" @selected(old('status', $sample->status ?? 'requested') === $value)>{{ ucfirst(str_replace('_', ' ', $value)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Size Ref</label>
        <input type="text" name="size_ref" class="form-control" value="{{ old('size_ref', $sample->size_ref ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Color Ref</label>
        <input type="text" name="color_ref" class="form-control" value="{{ old('color_ref', $sample->color_ref ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Request Date</label>
        <input type="date" name="request_date" class="form-control" value="{{ old('request_date', optional($sample->request_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Required Date</label>
        <input type="date" name="required_date" class="form-control" value="{{ old('required_date', optional($sample->required_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $sample->remarks ?? '') }}</textarea>
    </div>
</div>
