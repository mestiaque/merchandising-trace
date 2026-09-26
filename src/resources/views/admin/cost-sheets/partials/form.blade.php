{{--
    Open Cost Sheet entry, laid out like the printed sheet.
    props: costSheet (optional, edit), prefill (array from ?inquiry_id / ?style_id),
           stylesOptions, inquiriesOptions, buyersOptions, currenciesOptions, itemsOptions,
           uomsOptions, suppliersOptions (id => name)
    Lines are per DOZEN: fabric total = cons × price; other sections = cons × price × 12.
--}}
@php
    $cs = $costSheet ?? null;
    $prefill = $prefill ?? [];
    $val = fn (string $field, $default = null) => old($field, $cs ? $cs->{$field} : ($prefill[$field] ?? $default));
    $cmPerDozen = old('cm_per_dozen', $cs
        ? ((float) $cs->cm_cost ? round((float) $cs->cm_cost * 12, 4) : '')
        : ($prefill['cm_per_dozen'] ?? ''));
    $lines = collect(old('items', $cs
        ? $cs->items->map(fn ($i) => $i->only(['group', 'item_id', 'description', 'supplier_name', 'consumption', 'uom_id', 'rate']))->all()
        : []))->groupBy('group');
    // A stored CM equal to the SMV formula was auto-derived: keep it following SMV/CPM.
    $cmManual = $cmPerDozen !== '' && $cmPerDozen !== null
        && ! (old('cm_per_dozen') === null && $cs && abs((float) $cs->cm_cost - $cs->calcCm()) < 0.0001);
    $groupsMeta = \ME\MerchandisingTrace\Models\CostSheetItem::GROUPS;
    $dozenUomId = $uomsOptions->first(fn ($u) => in_array(strtoupper($u->code ?? ''), ['DZN', 'DOZ', 'DZ'], true))?->id;
    $rowIndex = 0;
@endphp

<div class="csf">
    {{-- Costing basis: pick the tech pack and/or inquiry — the rest fills in --}}
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Tech Pack / Style</label>
            <select name="style_id" id="csStyle" class="form-control form-control-sm merch-select2">
                <option value="">— None (style not created yet) —</option>
                @foreach($stylesOptions as $s)
                    <option value="{{ $s->id }}" @selected($val('style_id') == $s->id)>{{ $s->style_no }} — {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Inquiry</label>
            <select name="inquiry_id" id="csInquiry" class="form-control form-control-sm merch-select2">
                <option value="">— None —</option>
                @foreach($inquiriesOptions as $inq)
                    <option value="{{ $inq->id }}" @selected($val('inquiry_id') == $inq->id)>{{ $inq->inquiry_no }}{{ $inq->style_ref ? ' — ' . $inq->style_ref : '' }} ({{ $inq->buyer->name ?? '' }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-12"><div class="form-text mt-n2 mb-3" id="csLookupNote">Pick a tech pack or an inquiry and its buyer, style, qty, SMV and CM fill in automatically. With neither, just type the style below.</div></div>
    </div>

    <div class="csf-title">
        <div class="csf-co">{{ config('merchandising-trace.company.name') }}</div>
        <div class="csf-sub">OPEN COST SHEET</div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <table class="table table-bordered table-sm csf-head">
                <tr>
                    <th>Buyer <span class="text-danger">*</span></th>
                    <td>
                        <select name="buyer_id" id="csBuyer" class="form-control form-control-sm merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($buyersOptions as $b)
                                <option value="{{ $b->id }}" @selected($val('buyer_id') == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr><th>Description</th><td><input type="text" name="garment_description" id="csDescription" class="form-control form-control-sm" maxlength="255" placeholder="e.g. DENIM JACKET" value="{{ $val('garment_description') }}"></td></tr>
                <tr><th>Style <span class="text-danger">*</span></th><td><input type="text" name="style_ref" id="csStyleRef" class="form-control form-control-sm" maxlength="150" value="{{ $val('style_ref') }}"></td></tr>
                <tr><th>Size</th><td><input type="text" name="size_range" class="form-control form-control-sm" maxlength="100" placeholder="e.g. S-XL" value="{{ $val('size_range') }}"></td></tr>
                <tr>
                    <th>Order</th>
                    <td><div class="input-group input-group-sm"><input type="number" min="0" name="order_qty" id="csOrderQty" class="form-control form-control-sm" value="{{ $val('order_qty') }}"><div class="input-group-append"><span class="input-group-text">Pcs</span></div></div></td>
                </tr>
            </table>
        </div>
        <div class="col-lg-5">
            <table class="table table-bordered table-sm csf-head">
                <tr><th>Date</th><td><input type="date" name="costing_date" class="form-control form-control-sm" value="{{ optional($val('costing_date') ? \Illuminate\Support\Carbon::parse($val('costing_date')) : now())->format('Y-m-d') }}"></td></tr>
                @foreach(['front_image' => 'Front Picture', 'back_image' => 'Back Picture', 'sketch_image' => 'Tech Sketch'] as $field => $label)
                    <tr>
                        <th>{{ $label }}</th>
                        <td>
                            <input type="file" name="{{ $field }}" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp">
                            @if($cs?->{$field})
                                <div class="small mt-1 d-flex align-items-center gap-2">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($cs->{$field}) }}" alt="" style="height:32px;">
                                    <label class="mb-0"><input type="checkbox" name="remove_images[]" value="{{ $field }}"> remove</label>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr><td colspan="2" class="small text-muted">No picture uploaded? The tech pack's images are used.</td></tr>
            </table>
        </div>
    </div>

    {{-- A–F sections --}}
    @foreach($groupsMeta as $group => [$letter, $title, $totalLabel])
        <div class="csf-section" data-group="{{ $group }}">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-1">{{ $letter }}. {{ $title }}
                    <small class="text-muted">— total = cons × price{{ $group === 'fabric' ? '' : ' × 12' }}</small>
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-line="{{ $group }}"><i class="fa-solid fa-plus"></i> Add Row</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-1">
                    <thead>
                        <tr>
                            <th style="width:48px">SL</th>
                            <th style="min-width:170px">Item (library)</th>
                            <th style="min-width:180px">{{ $group === 'fabric' ? 'Fabric' : 'Details' }}</th>
                            <th style="min-width:160px">Supplier</th>
                            <th style="width:110px">Consumption <small class="text-muted">/Dz</small></th>
                            <th style="min-width:110px">Units</th>
                            <th style="width:120px">Unit Price{{ $group === 'fabric' ? ' (YD)' : '' }}</th>
                            <th style="width:120px" class="text-right">Total /Dz</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody data-lines="{{ $group }}">
                        @foreach($lines->get($group, collect()) as $line)
                            @include('merchandising-trace::admin.cost-sheets.partials.line-row', ['index' => $rowIndex++, 'group' => $group, 'line' => $line, 'dozenUomId' => $dozenUomId])
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="csf-total">
                            <td colspan="7" class="text-right">{{ $letter }}. {{ strtoupper($totalLabel) }}</td>
                            <td class="text-right" data-group-total="{{ $group }}">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach

    <div class="row mt-3">
        {{-- CM & commercial inputs --}}
        <div class="col-lg-5">
            <table class="table table-bordered table-sm csf-head">
                <tr><th>SMV</th><td><input type="number" step="0.01" min="0" name="smv" id="csSmv" class="form-control form-control-sm" value="{{ $val('smv') }}"></td></tr>
                <tr><th>CPM <small class="text-muted">(cost / minute)</small></th><td><input type="number" step="0.0001" min="0" name="cm_minute_rate" id="csCpm" class="form-control form-control-sm" value="{{ $val('cm_minute_rate') }}"></td></tr>
                <tr><th>Efficiency %</th><td><input type="number" step="0.01" min="1" max="200" name="efficiency_percent" id="csEff" class="form-control form-control-sm" value="{{ $val('efficiency_percent', 100) }}"></td></tr>
                <tr>
                    <th>CM / Dz</th>
                    <td>
                        <input type="number" step="0.0001" min="0" name="cm_per_dozen" id="csCm" class="form-control form-control-sm" value="{{ $cmPerDozen }}" data-manual="{{ $cmManual ? 1 : 0 }}">
                        <span class="form-text" id="csCmHint">Auto = SMV ÷ efficiency × CPM × 12. Type a value (e.g. the tech pack's Confirm CM) to override.</span>
                    </td>
                </tr>
                <tr><th>Commercial %</th><td><input type="number" step="0.01" min="0" max="100" name="commercial_percent" id="csCommercial" class="form-control form-control-sm" value="{{ $val('commercial_percent', 5) }}"></td></tr>
                <tr>
                    <th>Currency</th>
                    <td>
                        <select name="currency_id" class="form-control form-control-sm merch-select2">
                            <option value="">— Select —</option>
                            @foreach($currenciesOptions as $c)
                                <option value="{{ $c->id }}" @selected($val('currency_id') == $c->id)>{{ $c->code }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Price Type</th>
                    <td>
                        <select name="price_type" class="form-control form-control-sm">
                            @foreach(\ME\MerchandisingTrace\Models\CostSheet::PRICE_TYPES as $pt)
                                <option value="{{ $pt }}" @selected($val('price_type', 'FOB') === $pt)>{{ $pt }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr><th>Buyer Target Price <small class="text-muted">/Pc</small></th><td><input type="number" step="0.0001" min="0" name="buyer_target_price" id="csTarget" class="form-control form-control-sm" value="{{ $val('buyer_target_price') }}"></td></tr>
                <tr><th>Final Price <small class="text-muted">/Pc</small></th><td><input type="number" step="0.0001" min="0" name="final_price" class="form-control form-control-sm" value="{{ $val('final_price') }}"></td></tr>
            </table>
        </div>

        {{-- Live summary — same figures as the printed sheet --}}
        <div class="col-lg-7">
            <table class="table table-bordered table-sm csf-summary">
                <tr><td colspan="4" class="csf-sum-h">SUMMARY</td></tr>
                <tr class="text-primary font-weight-bold text-center"><td></td><td>DZN</td><td>PC</td><td>%</td></tr>
                @foreach($groupsMeta as $group => [$letter, , $totalLabel])
                    <tr>
                        <td>{{ $letter }}. {{ strtoupper($totalLabel) }}</td>
                        <td class="text-right" data-sum="{{ $group }}-dz"></td>
                        <td class="text-right" data-sum="{{ $group }}-pc"></td>
                        <td class="text-right text-primary" data-sum="{{ $group }}-pct"></td>
                    </tr>
                @endforeach
                <tr class="font-weight-bold"><td>TOTAL AMOUNT</td><td class="text-right" data-sum="mat-dz"></td><td class="text-right" data-sum="mat-pc"></td><td></td></tr>
                <tr><td><strong>CM</strong> <span class="float-right">SMV <span data-sum="smv"></span></span></td><td class="text-right csf-hl" data-sum="cm-dz"></td><td class="text-right" data-sum="cm-pc"></td><td class="text-right text-primary" data-sum="cm-pct"></td></tr>
                <tr class="font-weight-bold"><td>SUB TOTAL FOB PER</td><td class="text-right" data-sum="sub-dz"></td><td class="text-right" data-sum="sub-pc"></td><td></td></tr>
                <tr><td><strong>COMMERCIAL COST</strong> <span class="float-right" data-sum="com-rate"></span></td><td class="text-right" data-sum="com-dz"></td><td class="text-right" data-sum="com-pc"></td><td class="text-right text-primary" data-sum="com-rate2"></td></tr>
                <tr data-sum-row="other" hidden><td>OTHER COST <small class="text-muted">(freight / testing / overhead)</small></td><td class="text-right" data-sum="other-dz"></td><td class="text-right" data-sum="other-pc"></td><td></td></tr>
                <tr data-sum-row="profit" hidden><td>PROFIT <span class="float-right" data-sum="profit-rate"></span></td><td class="text-right" data-sum="profit-dz"></td><td class="text-right" data-sum="profit-pc"></td><td></td></tr>
                <tr class="font-weight-bold"><td>TOTAL FOB PER DOZ</td><td class="text-right" data-sum="fob-dz"></td><td></td><td></td></tr>
                <tr class="font-weight-bold"><td>TOTAL FOB PER PCS</td><td class="text-right csf-hl" data-sum="fob-pc"></td><td class="text-center">TTL B2B</td><td class="text-right text-primary" data-sum="b2b"></td></tr>
                <tr><td colspan="4" class="small" data-sum="target-note"></td></tr>
            </table>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control form-control-sm" rows="2">{{ old('remarks', $cs->remarks ?? '') }}</textarea>
    </div>
</div>

{{-- Pre-Open-Cost-Sheet extras of an older sheet (0 on new ones), so the live summary matches its print. --}}
<div id="csLegacy" hidden
    data-other-pc="{{ $cs ? $cs->otherCost() - (float) $cs->accessories_cost : 0 }}"
    data-profit-pct="{{ $cs ? (float) $cs->profit_percent : 0 }}"
    data-flat-commercial-pc="{{ $cs && ! (float) $cs->commercial_percent ? (float) $cs->commercial_cost : 0 }}"></div>

<template id="csLineTemplate">
    @include('merchandising-trace::admin.cost-sheets.partials.line-row', ['index' => '__INDEX__', 'group' => '__GROUP__', 'line' => [], 'dozenUomId' => null])
</template>
<datalist id="csSupplierList">
    @foreach($suppliersOptions as $name)
        <option value="{{ $name }}"></option>
    @endforeach
</datalist>

@push('css')
<style>
    .csf-title { text-align: center; margin: .25rem 0 .75rem; }
    .csf-co { color: #d99a00; font-weight: 700; font-size: 1.4rem; letter-spacing: 1px; }
    .csf-sub { color: #1f4ea0; font-weight: 700; }
    .csf-head th { width: 34%; background: #f6f6f6; vertical-align: middle; font-size: .8rem; text-transform: uppercase; }
    .csf-section { border: 1px solid #dee2e6; border-radius: .4rem; padding: .5rem .75rem; margin-bottom: .75rem; }
    .csf-section thead th { background: #f6f6f6; font-size: .75rem; text-transform: uppercase; white-space: nowrap; }
    .csf-total td { background: #fff4b8; color: #1f4ea0; font-weight: 700; }
    .csf-summary td { padding: .2rem .5rem; }
    .csf-sum-h { background: #f4c542; font-weight: 700; text-align: center; }
    .csf-hl { background: #ffff00; font-weight: 700; }
    .csf [data-line-total] { font-weight: 600; }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jq = typeof $ !== 'undefined' ? $ : null;
        const root = document.querySelector('.csf');
        const num = function (v) { const n = parseFloat(v); return isNaN(n) ? 0 : n; };
        const fmt = function (v) { return v ? v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'; };
        const pct = function (v) { return v.toFixed(2) + ' %'; };
        const factor = function (group) { return group === 'fabric' ? 1 : 12; };
        const dozenUomId = @json($dozenUomId);
        let rowIndex = 1000000;

        function setVal(el, value, triggerSelect2) {
            if (!el || value === null || value === undefined || value === '') { return; }
            el.value = value;
            if (triggerSelect2 && jq) { jq(el).trigger('change.select2'); }
        }

        // ---- lines ---------------------------------------------------------
        function addLine(group, data) {
            const tpl = document.getElementById('csLineTemplate').innerHTML
                .replaceAll('__INDEX__', rowIndex++).replaceAll('__GROUP__', group);
            const wrap = document.createElement('table');
            wrap.innerHTML = '<tbody>' + tpl + '</tbody>';
            const row = wrap.querySelector('tr');
            if (group !== 'fabric' && dozenUomId) { row.querySelector('[name$="[uom_id]"]').value = dozenUomId; }
            Object.entries(data || {}).forEach(function ([key, value]) {
                const el = row.querySelector('[name$="[' + key + ']"]');
                if (el && value !== null && value !== undefined) { el.value = value; }
            });
            root.querySelector('[data-lines="' + group + '"]').appendChild(row);
            prodSelect2Init(document);
            recalc();
            return row;
        }

        root.addEventListener('click', function (e) {
            const add = e.target.closest('[data-add-line]');
            if (add) { addLine(add.dataset.addLine); return; }
            if (e.target.closest('[data-remove-row]')) {
                e.target.closest('tr').remove();
                recalc();
            }
        });

        // Picking a library item fills what the item master already knows.
        if (jq) {
            jq(root).on('change', 'select[name$="[item_id]"]', function () {
                const opt = this.options[this.selectedIndex];
                const row = this.closest('tr');
                if (!opt || !opt.value) { return; }
                const desc = row.querySelector('[name$="[description]"]');
                const supplier = row.querySelector('[name$="[supplier_name]"]');
                const uom = row.querySelector('[name$="[uom_id]"]');
                const rate = row.querySelector('[name$="[rate]"]');
                if (!desc.value) { desc.value = opt.dataset.name || ''; }
                if (!supplier.value) { supplier.value = opt.dataset.supplier || ''; }
                if (row.closest('[data-lines="fabric"]') && opt.dataset.uom) { uom.value = opt.dataset.uom; jq(uom).trigger('change.select2'); }
                if (!rate.value && opt.dataset.price) { rate.value = opt.dataset.price; }
                recalc();
            });
        }

        // ---- CM ------------------------------------------------------------
        const cm = document.getElementById('csCm');
        cm.addEventListener('input', function () { cm.dataset.manual = cm.value === '' ? '0' : '1'; });
        function autoCm() {
            if (cm.dataset.manual === '1') { return; }
            const smv = num(document.getElementById('csSmv').value);
            const cpm = num(document.getElementById('csCpm').value);
            const eff = num(document.getElementById('csEff').value) || 100;
            cm.value = smv && cpm ? (smv / (eff / 100) * cpm * 12).toFixed(4) : '';
        }

        // ---- live totals (mirrors CostSheet::summary()) ---------------------
        function recalc() {
            autoCm();
            const groups = {};
            root.querySelectorAll('[data-lines]').forEach(function (body) {
                const group = body.dataset.lines;
                let total = 0;
                body.querySelectorAll('tr').forEach(function (row) {
                    const line = num(row.querySelector('[name$="[consumption]"]').value) * num(row.querySelector('[name$="[rate]"]').value) * factor(group);
                    row.querySelector('[data-line-total]').textContent = fmt(line);
                    total += line;
                });
                groups[group] = total;
                root.querySelector('[data-group-total="' + group + '"]').textContent = fmt(total);
            });

            let sl = 0;
            root.querySelectorAll('[data-lines] tr [data-sl]').forEach(function (cell) { cell.textContent = ++sl; });

            const mat = Object.values(groups).reduce(function (a, b) { return a + b; }, 0);
            const cmDz = num(cm.value);
            const sub = mat + cmDz;
            const legacy = document.getElementById('csLegacy').dataset;
            const comRate = num(document.getElementById('csCommercial').value);
            const com = comRate > 0 ? sub * comRate / 100 : num(legacy.flatCommercialPc) * 12;
            const other = num(legacy.otherPc) * 12;
            const profitP = num(legacy.profitPct) / 100;
            const cost = sub + com + other;
            const fob = profitP > 0 && profitP < 1 ? cost / (1 - profitP) : cost;
            const share = function (v) { return fob > 0 ? v / fob * 100 : 0; };
            const put = function (key, text) { const el = root.querySelector('[data-sum="' + key + '"]'); if (el) { el.textContent = text; } };

            Object.entries(groups).forEach(function ([g, dz]) {
                put(g + '-dz', fmt(dz)); put(g + '-pc', fmt(dz / 12)); put(g + '-pct', pct(share(dz)));
            });
            put('mat-dz', fmt(mat)); put('mat-pc', fmt(mat / 12));
            put('smv', document.getElementById('csSmv').value || '-');
            put('cm-dz', fmt(cmDz)); put('cm-pc', fmt(cmDz / 12)); put('cm-pct', pct(share(cmDz)));
            put('sub-dz', fmt(sub)); put('sub-pc', fmt(sub / 12));
            put('com-rate', comRate + ' %'); put('com-rate2', comRate + ' %');
            put('com-dz', fmt(com)); put('com-pc', fmt(com / 12));
            put('other-dz', fmt(other)); put('other-pc', fmt(other / 12));
            put('profit-dz', fmt(fob - cost)); put('profit-pc', fmt((fob - cost) / 12)); put('profit-rate', (profitP * 100) + ' %');
            root.querySelector('[data-sum-row="other"]').hidden = !other;
            root.querySelector('[data-sum-row="profit"]').hidden = !(fob - cost);
            put('fob-dz', fmt(fob)); put('fob-pc', fmt(fob / 12)); put('b2b', pct(share(mat)));

            const target = num(document.getElementById('csTarget').value);
            put('target-note', target && fob
                ? 'Buyer target ' + fmt(target) + ' / pc → ' + (fob / 12 <= target ? 'within target by ' : 'over target by ') + fmt(Math.abs(target - fob / 12)) + ' / pc'
                : '');
        }
        root.addEventListener('input', recalc);

        // ---- style / inquiry auto-fill (item: never enter the same info twice)
        const note = document.getElementById('csLookupNote');
        function fetchJson(url) {
            return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (r) {
                if (!r.ok) { throw new Error(r.status); }
                return r.json();
            });
        }
        function fillFromInquiry(inq) {
            setVal(document.getElementById('csBuyer'), inq.buyer_id, true);
            setVal(document.getElementById('csStyleRef'), inq.style_ref);
            setVal(document.getElementById('csDescription'), inq.garment_description);
            setVal(document.getElementById('csOrderQty'), inq.order_qty);
            setVal(document.getElementById('csTarget'), inq.unit_price);
        }
        const styleSel = document.getElementById('csStyle');
        const inquirySel = document.getElementById('csInquiry');
        let syncing = false;

        function onStyle() {
            if (syncing || !styleSel.value) { return; }
            fetchJson('{{ route('merchandising-trace.lookup.style', '__ID__') }}'.replace('__ID__', styleSel.value)).then(function (s) {
                if (s.inquiry) {
                    fillFromInquiry(s.inquiry);
                    syncing = true;
                    setVal(inquirySel, s.inquiry.id, true);
                    syncing = false;
                }
                setVal(document.getElementById('csBuyer'), s.buyer_id, true);
                setVal(document.getElementById('csStyleRef'), s.style_no);
                setVal(document.getElementById('csDescription'), s.garment_description);
                setVal(document.getElementById('csSmv'), s.smv);
                if (s.confirm_cm !== null && s.confirm_cm !== undefined) {
                    cm.value = s.confirm_cm; cm.dataset.manual = '1';
                }
                note.textContent = 'Filled from tech pack ' + s.style_no + (s.inquiry ? ' / inquiry ' + s.inquiry.inquiry_no : '') + (s.confirm_cm ? ' — CM taken from its Confirm CM.' : '.');
                recalc();
            }).catch(function () { note.textContent = 'Could not load that tech pack — fill the fields manually.'; });
        }
        function onInquiry() {
            if (syncing || !inquirySel.value) { return; }
            fetchJson('{{ route('merchandising-trace.lookup.inquiry', '__ID__') }}'.replace('__ID__', inquirySel.value)).then(function (inq) {
                fillFromInquiry(inq);
                if (inq.tech_pack && !styleSel.value) {
                    syncing = true;
                    setVal(styleSel, inq.tech_pack.id, true);
                    syncing = false;
                    onStyle();
                }
                note.textContent = 'Filled from inquiry ' + inq.inquiry_no + '.';
                recalc();
            }).catch(function () { note.textContent = 'Could not load that inquiry — fill the fields manually.'; });
        }
        if (jq) {
            jq(styleSel).on('change', onStyle);
            jq(inquirySel).on('change', onInquiry);
        }

        recalc();
    });
</script>
@endpush
