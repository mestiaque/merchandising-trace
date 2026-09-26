{{-- props: bom (optional, for edit), stylesOptions, itemsOptions, uomsOptions, suppliersOptions, colorsOptions, sizesOptions --}}
@php($bomType = old('bom_type', $bom->bom_type ?? 'file'))
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Style <span class="text-danger">*</span></label>
        <select name="style_id" id="bomStyle" class="form-control form-control-sm merch-select2" required {{ isset($bom) ? 'disabled' : '' }}>
            <option value="">— Select —</option>
            @foreach($stylesOptions as $style)
                <option value="{{ $style->id }}" data-buyer="{{ $style->buyer->name ?? '' }}" @selected(old('style_id', $bom->style_id ?? '') == $style->id)>{{ $style->style_no }} — {{ $style->name }}</option>
            @endforeach
        </select>
        @if(isset($bom))
            <input type="hidden" name="style_id" value="{{ $bom->style_id }}">
            <span class="form-text">A new version keeps the same style — create a fresh BOM to target a different style.</span>
        @endif
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Buyer</label>
        <input type="text" id="bomBuyer" class="form-control form-control-sm bg-light" readonly tabindex="-1">
    </div>

    <div class="col-12 mb-3">
        <label class="form-label d-block">BOM Option <span class="text-danger">*</span></label>
        <div class="btn-group merch-btn-radio" role="group">
            @foreach(\ME\MerchandisingTrace\Models\Bom::TYPES as $value => $label)
                <input type="radio" name="bom_type" id="bomType_{{ $value }}" value="{{ $value }}" autocomplete="off" @checked($bomType === $value)>
                <label class="btn btn-outline-primary {{ $bomType === $value ? 'active' : '' }} btn-sm" for="bomType_{{ $value }}">
                    <i class="fa-solid {{ $value === 'file' ? 'fa-file-arrow-up' : 'fa-list-check' }}"></i> {{ $label }}
                </label>
            @endforeach
        </div>
        <span class="form-text d-block">Upload the PDF when the buyer provides the BOM; otherwise build it here line by line.</span>
    </div>
</div>

<div data-bom-panel="file" class="mb-3">
    <label class="form-label">BOM (PDF) <span class="text-danger">*</span></label>
    <input type="file" name="bom_file" class="form-control form-control-sm" accept="application/pdf">
    @if(!empty($bom?->bom_file))
        <div class="mt-1 small">
            Current file:
            <a href="{{ route('merchandising-trace.boms.file.view', $bom) }}" target="_blank" rel="noopener">View</a>
            &middot;
            <a href="{{ route('merchandising-trace.boms.file.download', $bom) }}">Download</a>
            <span class="text-muted">— leave empty to keep it.</span>
        </div>
    @endif
</div>

<div data-bom-panel="manual" class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">BOM Lines <small class="text-muted">(consumption per piece)</small></h6>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="bomLoadCostSheetBtn" title="Copy fabric &amp; trims lines from this style's latest cost sheet">
                <i class="fa-solid fa-file-import"></i> Load from Cost Sheet
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addBomItemBtn"><i class="fa-solid fa-plus"></i> Add Row</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead>
                <tr>
                    <th style="min-width:200px">Item</th><th style="min-width:140px">Color</th><th style="min-width:120px">Size</th>
                    <th style="min-width:120px">Part</th><th style="width:110px">Consumption</th><th style="min-width:120px">UOM</th>
                    <th style="width:100px">Wastage %</th><th style="width:110px">Rate</th><th style="min-width:160px">Supplier</th><th style="width:40px"></th>
                </tr>
            </thead>
            <tbody id="bomRowsBody">
                @php($lines = old('items', isset($bom) && $bom->items->isNotEmpty() ? $bom->items->map(fn ($i) => $i->toArray())->all() : [[]]))
                @foreach($lines as $index => $line)
                    @include('merchandising-trace::admin.boms.partials.line-row', ['index' => $index, 'line' => $line])
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Remarks</label>
    <textarea name="remarks" class="form-control form-control-sm" rows="2">{{ old('remarks', $bom->remarks ?? '') }}</textarea>
</div>

<template id="bomRowTemplate">
    @include('merchandising-trace::admin.boms.partials.line-row', ['index' => '__INDEX__', 'line' => []])
</template>

@push('css')
<style>
    .merch-btn-radio input[type="radio"] { position: absolute; clip: rect(0, 0, 0, 0); pointer-events: none; }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowIndex = 1000000;
        const body = document.getElementById('bomRowsBody');

        function setPanel(type) {
            document.querySelectorAll('[data-bom-panel]').forEach(function (panel) {
                const on = panel.dataset.bomPanel === type;
                panel.style.display = on ? '' : 'none';
                // Disabled inputs are neither validated nor submitted.
                panel.querySelectorAll('input, select, textarea, button').forEach(function (el) { el.disabled = !on; });
            });
            document.querySelectorAll('input[name="bom_type"]').forEach(function (r) {
                document.querySelector('label[for="' + r.id + '"]').classList.toggle('active', r.checked);
            });
        }
        document.querySelectorAll('input[name="bom_type"]').forEach(function (r) {
            r.addEventListener('change', function () { setPanel(this.value); });
        });
        setPanel(document.querySelector('input[name="bom_type"]:checked')?.value || 'file');

        function addRow(data) {
            const tpl = document.getElementById('bomRowTemplate');
            const wrap = document.createElement('table');
            wrap.innerHTML = '<tbody>' + tpl.innerHTML.replaceAll('__INDEX__', rowIndex++) + '</tbody>';
            const row = wrap.querySelector('tr');
            Object.entries(data || {}).forEach(function ([key, value]) {
                const el = row.querySelector('[name$="[' + key + ']"]');
                if (el && value !== null && value !== undefined) { el.value = value; }
            });
            body.appendChild(row);
            prodSelect2Init(document);
        }

        function isBlankRow(row) {
            return Array.from(row.querySelectorAll('select, input')).every(function (el) {
                return !el.value || el.name.endsWith('[wastage_percent]');
            });
        }

        document.getElementById('addBomItemBtn')?.addEventListener('click', function () { addRow(); });
        body.addEventListener('click', function (e) {
            if (e.target.closest('[data-remove-row]')) {
                e.target.closest('tr').remove();
            }
        });

        // Buyer display + "Load from Cost Sheet" (no re-typing what costing already has).
        const styleSel = document.getElementById('bomStyle');
        function showBuyer() {
            const opt = styleSel.options[styleSel.selectedIndex];
            document.getElementById('bomBuyer').value = opt && opt.value ? (opt.dataset.buyer || '') : '';
        }
        if (typeof $ !== 'undefined') { $(styleSel).on('change', showBuyer); } else { styleSel.addEventListener('change', showBuyer); }
        showBuyer();

        document.getElementById('bomLoadCostSheetBtn')?.addEventListener('click', function () {
            const styleId = styleSel.value;
            if (!styleId) { alert('Select a style first.'); return; }
            const btn = this;
            btn.disabled = true;
            fetch('{{ route('merchandising-trace.lookup.style', '__ID__') }}'.replace('__ID__', styleId), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
                .then(function (data) {
                    const lines = (data.cost_sheet && data.cost_sheet.bom_lines) || [];
                    if (!lines.length) { alert('This style has no cost sheet lines to copy.'); return; }
                    body.querySelectorAll('tr').forEach(function (row) { if (isBlankRow(row)) { row.remove(); } });
                    lines.forEach(addRow);
                })
                .catch(function () { alert('Could not load the cost sheet for this style.'); })
                .finally(function () { btn.disabled = false; });
        });
    });
</script>
@endpush
