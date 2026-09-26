<div class="mb-3">
    <label class="form-label">Style <span class="text-danger">*</span></label>
    <select name="style_id" class="form-control form-control-sm merch-select2" required>
        <option value="">— Select —</option>
        @foreach($stylesOptions as $s)
            <option value="{{ $s->id }}" @selected(old('style_id', $riskAssessment->style_id ?? '') == $s->id)>{{ $s->style_no }} — {{ $s->name }}</option>
        @endforeach
    </select>
</div>
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Season</label>
        <select name="season_id" class="form-control form-control-sm merch-select2">
            <option value="">— Select —</option>
            @foreach($seasonsOptions as $s)
                <option value="{{ $s->id }}" @selected(old('season_id', $riskAssessment->season_id ?? '') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Collection Name</label>
        <input type="text" name="collection_name" class="form-control form-control-sm" value="{{ old('collection_name', $riskAssessment->collection_name ?? '') }}">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Category</label>
    <input type="text" name="category" class="form-control form-control-sm" value="{{ old('category', $riskAssessment->category ?? '') }}">
</div>
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Sewing Factory</label>
        <select name="sewing_factory_id" class="form-control form-control-sm merch-select2">
            <option value="">— N/A —</option>
            @foreach($factoriesOptions as $f)
                <option value="{{ $f->id }}" @selected(old('sewing_factory_id', $riskAssessment->sewing_factory_id ?? '') == $f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Print Factory</label>
        <select name="print_factory_id" class="form-control form-control-sm merch-select2">
            <option value="">— N/A —</option>
            @foreach($factoriesOptions as $f)
                <option value="{{ $f->id }}" @selected(old('print_factory_id', $riskAssessment->print_factory_id ?? '') == $f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Embroidery Factory</label>
        <select name="embroidery_factory_id" class="form-control form-control-sm merch-select2">
            <option value="">— N/A —</option>
            @foreach($factoriesOptions as $f)
                <option value="{{ $f->id }}" @selected(old('embroidery_factory_id', $riskAssessment->embroidery_factory_id ?? '') == $f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Wash Factory</label>
        <select name="wash_factory_id" class="form-control form-control-sm merch-select2">
            <option value="">— N/A —</option>
            @foreach($factoriesOptions as $f)
                <option value="{{ $f->id }}" @selected(old('wash_factory_id', $riskAssessment->wash_factory_id ?? '') == $f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row">
<div class="col-12 mb-3">
    <label class="form-label">Design Risk Findings</label>
    <textarea name="design_risk" class="form-control form-control-sm" rows="2">{{ old('design_risk', $riskAssessment->design_risk ?? '') }}</textarea>
</div>
<div class="col-12 mb-3">
    <label class="form-label">Materials Risk Findings</label>
    <textarea name="materials_risk" class="form-control form-control-sm" rows="2">{{ old('materials_risk', $riskAssessment->materials_risk ?? '') }}</textarea>
</div>
<div class="col-12 mb-3">
    <label class="form-label">Components Risk Findings</label>
    <textarea name="components_risk" class="form-control form-control-sm" rows="2">{{ old('components_risk', $riskAssessment->components_risk ?? '') }}</textarea>
</div>
<div class="col-12 mb-3">
    <label class="form-label">Production/Process Risk Findings</label>
    <textarea name="process_risk" class="form-control form-control-sm" rows="3">{{ old('process_risk', $riskAssessment->process_risk ?? '') }}</textarea>
</div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="riskAssessmentActive{{ $riskAssessment->id ?? 'new' }}" @checked(old('is_active', $riskAssessment->is_active ?? true))>
    <label class="custom-control-label" for="riskAssessmentActive{{ $riskAssessment->id ?? 'new' }}">Active</label>
</div>
</div></div>
