<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Style No <span class="text-danger">*</span></label>
        <input type="text" name="style_no" class="form-control" value="{{ old('style_no', $style->style_no ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">PO No</label>
        <input type="text" name="po_no" class="form-control" value="{{ old('po_no', $style->po_no ?? '') }}">
    </div>
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
    <label class="form-label">Buyer <span class="text-danger">*</span></label>
    <select name="buyer_id" class="form-control merch-select2" required>
        <option value="">— Select —</option>
        @foreach($buyersOptions as $b)
            <option value="{{ $b->id }}" @selected(old('buyer_id', $style->buyer_id ?? '') == $b->id)>{{ $b->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Season</label>
    <select name="season_id" class="form-control merch-select2">
        <option value="">— Select —</option>
        @foreach($seasonsOptions as $s)
            <option value="{{ $s->id }}" @selected(old('season_id', $style->season_id ?? '') == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Merchandiser</label>
    <select name="merchandiser_id" class="form-control merch-select2">
        <option value="">— Select —</option>
        @foreach($merchandisersOptions as $m)
            <option value="{{ $m->id }}" @selected(old('merchandiser_id', $style->merchandiser_id ?? '') == $m->id)>{{ $m->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Wash Type</label>
    <select name="wash_type_id" class="form-control merch-select2">
        <option value="">— Select —</option>
        @foreach($washTypesOptions as $w)
            <option value="{{ $w->id }}" @selected(old('wash_type_id', $style->wash_type_id ?? '') == $w->id)>{{ $w->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Product Type</label>
    <select name="product_type_id" class="form-control merch-select2">
        <option value="">— Select —</option>
        @foreach($productTypesOptions as $pt)
            <option value="{{ $pt->id }}" @selected(old('product_type_id', $style->product_type_id ?? '') == $pt->id)>{{ $pt->name }}</option>
        @endforeach
    </select>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">SMV</label>
        <input type="number" step="0.01" name="smv" class="form-control" value="{{ old('smv', $style->smv ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Cost SMV</label>
        <input type="number" step="0.01" name="cost_smv" class="form-control" value="{{ old('cost_smv', $style->cost_smv ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Target CM</label>
        <input type="number" step="0.0001" name="target_cm" class="form-control" value="{{ old('target_cm', $style->target_cm ?? '') }}">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Fabric Description</label>
    <textarea name="fabric_description" class="form-control" rows="2">{{ old('fabric_description', $style->fabric_description ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Development Status</label>
    <select name="development_status" class="form-control">
        @foreach(\ME\MerchandisingTrace\Models\Style::DEVELOPMENT_STATUSES as $val)
            <option value="{{ $val }}" @selected(old('development_status', $style->development_status ?? 'new') === $val)>{{ ucfirst(str_replace('_', ' ', $val)) }}</option>
        @endforeach
    </select>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Fabric Sourced By</label>
        <select name="fabric_sourced_by" class="form-control">
            <option value="self" @selected(old('fabric_sourced_by', $style->fabric_sourced_by ?? 'self') === 'self')>Us (supplier handling)</option>
            <option value="buyer" @selected(old('fabric_sourced_by', $style->fabric_sourced_by ?? 'self') === 'buyer')>Buyer supplies directly</option>
        </select>
    </div>
    <div class="col-md-6 mb-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="requires_dev_sample" value="0">
            <input type="checkbox" name="requires_dev_sample" value="1" class="form-check-input" id="styleReqDevSample{{ $style->id ?? 'new' }}" @checked(old('requires_dev_sample', $style->requires_dev_sample ?? true))>
            <label class="form-check-label" for="styleReqDevSample{{ $style->id ?? 'new' }}">Requires Dev/Sample Stage</label>
        </div>
    </div>
</div>
<div class="form-check form-switch mb-2">
    <input type="hidden" name="is_repeat" value="0">
    <input type="checkbox" name="is_repeat" value="1" class="form-check-input" id="styleRepeat{{ $style->id ?? 'new' }}" @checked(old('is_repeat', $style->is_repeat ?? false))>
    <label class="form-check-label" for="styleRepeat{{ $style->id ?? 'new' }}">Repeat Style</label>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="styleActive{{ $style->id ?? 'new' }}" @checked(old('is_active', $style->is_active ?? true))>
    <label class="form-check-label" for="styleActive{{ $style->id ?? 'new' }}">Active</label>
</div>
<div class="mb-3 mt-2">
    <label class="form-label">Tech Pack (PDF)</label>
    <input type="file" name="tech_pack_file" class="form-control" accept="application/pdf">
    @if(!empty($style?->tech_pack_file))
        <div class="mt-1 small">
            Current file:
            <a href="{{ route('merchandising-trace.styles.tech-pack.view', $style) }}" target="_blank" rel="noopener">View</a>
            &middot;
            <a href="{{ route('merchandising-trace.styles.tech-pack.download', $style) }}">Download</a>
        </div>
    @endif
</div>
<div class="mb-3">
    <label class="form-label">Sales Contract (PDF)</label>
    <input type="file" name="sales_contract_file" class="form-control" accept="application/pdf">
    @if(!empty($style?->sales_contract_file))
        <div class="mt-1 small">
            Current file:
            <a href="{{ route('merchandising-trace.styles.sales-contract.view', $style) }}" target="_blank" rel="noopener">View</a>
            &middot;
            <a href="{{ route('merchandising-trace.styles.sales-contract.download', $style) }}">Download</a>
        </div>
    @endif
</div>
