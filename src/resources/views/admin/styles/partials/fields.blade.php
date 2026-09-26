<div class="mb-3">
    <label class="form-label">From Inquiry</label>
    <select name="inquiry_id" class="form-control form-control-sm merch-select2" data-style-inquiry>
        <option value="">— None —</option>
        @foreach($inquiriesOptions as $inq)
            @continue($inq->tech_pack_id && $inq->tech_pack_id != ($style->id ?? null))
            <option value="{{ $inq->id }}" @selected(old('inquiry_id', $style->inquiry_id ?? '') == $inq->id)
                data-buyer="{{ $inq->buyer_id }}" data-season="{{ $inq->season_id }}" data-merchandiser="{{ $inq->merchandiser_id }}"
                data-product-type="{{ $inq->product_type_id }}" data-style-ref="{{ $inq->style_ref }}"
                data-name="{{ $inq->productType->name ?? $inq->style_ref }}">
                {{ $inq->inquiry_no }}{{ $inq->style_ref ? ' — ' . $inq->style_ref : '' }} ({{ $inq->buyer->name ?? '' }})
            </option>
        @endforeach
    </select>
    <span class="form-text">Picking an inquiry fills buyer, season, merchant, product type and style from it.</span>
</div>
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Style No <span class="text-danger">*</span></label>
        <input type="text" name="style_no" class="form-control form-control-sm" value="{{ old('style_no', $style->style_no ?? '') }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">PO No</label>
        <input type="text" name="po_no" class="form-control form-control-sm" value="{{ old('po_no', $style->po_no ?? '') }}">
    </div>
</div>
<div class="row">
<div class="col-md-3 mb-3">
    <label class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $style->name ?? '') }}" required>
</div>
<div class="col-12 mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control form-control-sm" rows="2">{{ old('description', $style->description ?? '') }}</textarea>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Buyer <span class="text-danger">*</span></label>
    <select name="buyer_id" class="form-control form-control-sm merch-select2" required>
        <option value="">— Select —</option>
        @foreach($buyersOptions as $b)
            <option value="{{ $b->id }}" @selected(old('buyer_id', $style->buyer_id ?? '') == $b->id)>{{ $b->name }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Season</label>
    <select name="season_id" class="form-control form-control-sm merch-select2">
        <option value="">— Select —</option>
        @foreach($seasonsOptions as $s)
            <option value="{{ $s->id }}" @selected(old('season_id', $style->season_id ?? '') == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Merchandiser</label>
    <select name="merchandiser_id" class="form-control form-control-sm merch-select2">
        <option value="">— Select —</option>
        @foreach($merchandisersOptions as $m)
            <option value="{{ $m->id }}" @selected(old('merchandiser_id', $style->merchandiser_id ?? '') == $m->id)>{{ $m->name }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Wash Type</label>
    <select name="wash_type_id" class="form-control form-control-sm merch-select2">
        <option value="">— Select —</option>
        @foreach($washTypesOptions as $w)
            <option value="{{ $w->id }}" @selected(old('wash_type_id', $style->wash_type_id ?? '') == $w->id)>{{ $w->name }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Product Type</label>
    <select name="product_type_id" class="form-control form-control-sm merch-select2">
        <option value="">— Select —</option>
        @foreach($productTypesOptions as $pt)
            <option value="{{ $pt->id }}" @selected(old('product_type_id', $style->product_type_id ?? '') == $pt->id)>{{ $pt->name }}</option>
        @endforeach
    </select>
</div>
</div>
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">SMV</label>
        <input type="number" step="0.01" name="smv" class="form-control form-control-sm" value="{{ old('smv', $style->smv ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Cost SMV</label>
        <input type="number" step="0.01" name="cost_smv" class="form-control form-control-sm" value="{{ old('cost_smv', $style->cost_smv ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Target CM</label>
        <input type="number" step="0.0001" name="target_cm" class="form-control form-control-sm" value="{{ old('target_cm', $style->target_cm ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Confirm CM <small class="text-muted">/ Dz</small></label>
        <input type="number" step="0.0001" min="0" name="confirm_cm" class="form-control form-control-sm" value="{{ old('confirm_cm', $style->confirm_cm ?? '') }}">
    </div>
</div>
<div class="row">
<div class="col-12 mb-3">
    <label class="form-label">Fabric Description</label>
    <textarea name="fabric_description" class="form-control form-control-sm" rows="2">{{ old('fabric_description', $style->fabric_description ?? '') }}</textarea>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Development Status</label>
    <select name="development_status" class="form-control form-control-sm">
        @foreach(\ME\MerchandisingTrace\Models\Style::DEVELOPMENT_STATUSES as $val)
            <option value="{{ $val }}" @selected(old('development_status', $style->development_status ?? 'new') === $val)>{{ ucfirst(str_replace('_', ' ', $val)) }}</option>
        @endforeach
    </select>
</div>
</div>
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Fabric Sourced By</label>
        <select name="fabric_sourced_by" class="form-control form-control-sm">
            <option value="self" @selected(old('fabric_sourced_by', $style->fabric_sourced_by ?? 'self') === 'self')>Us (supplier handling)</option>
            <option value="buyer" @selected(old('fabric_sourced_by', $style->fabric_sourced_by ?? 'self') === 'buyer')>Buyer supplies directly</option>
        </select>
    </div>
    <div class="col-md-3 mb-3 d-flex align-items-end">
        <div class="custom-control custom-switch">
            <input type="hidden" name="requires_dev_sample" value="0">
            <input type="checkbox" name="requires_dev_sample" value="1" class="custom-control-input" id="styleReqDevSample{{ $style->id ?? 'new' }}" @checked(old('requires_dev_sample', $style->requires_dev_sample ?? true))>
            <label class="custom-control-label" for="styleReqDevSample{{ $style->id ?? 'new' }}">Requires Dev/Sample Stage</label>
        </div>
    </div>
</div>
<div class="custom-control custom-switch mb-2">
    <input type="hidden" name="is_repeat" value="0">
    <input type="checkbox" name="is_repeat" value="1" class="custom-control-input" id="styleRepeat{{ $style->id ?? 'new' }}" @checked(old('is_repeat', $style->is_repeat ?? false))>
    <label class="custom-control-label" for="styleRepeat{{ $style->id ?? 'new' }}">Repeat Style</label>
</div>
<div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="styleActive{{ $style->id ?? 'new' }}" @checked(old('is_active', $style->is_active ?? true))>
    <label class="custom-control-label" for="styleActive{{ $style->id ?? 'new' }}">Active</label>
</div>
<div class="mb-3 mt-2">
    <label class="form-label">Tech Pack (PDF)</label>
    <input type="file" name="tech_pack_file" class="form-control form-control-sm" accept="application/pdf">
    @if(!empty($style?->tech_pack_file))
        <div class="mt-1 small">
            Current file:
            <a href="{{ route('merchandising-trace.styles.tech-pack.view', $style) }}" target="_blank" rel="noopener">View</a>
            &middot;
            <a href="{{ route('merchandising-trace.styles.tech-pack.download', $style) }}">Download</a>
        </div>
    @endif
</div>

@once
@push('js')
<script>
    // Inquiry → tech pack auto-fill: only empty fields are filled, so an
    // edit never overwrites what the user already set.
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ === 'undefined') { return; }
        $(document).on('change', 'select[data-style-inquiry]', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt || !opt.value) { return; }
            const form = $(this).closest('form');
            const fill = function (name, value) {
                const el = form.find('[name="' + name + '"]');
                if (!value || !el.length || el.val()) { return; }
                el.val(value).trigger('change');
            };
            fill('buyer_id', opt.dataset.buyer);
            fill('season_id', opt.dataset.season);
            fill('merchandiser_id', opt.dataset.merchandiser);
            fill('product_type_id', opt.dataset.productType);
            fill('style_no', opt.dataset.styleRef);
            fill('name', opt.dataset.name);
        });
    });
</script>
@endpush
@endonce
