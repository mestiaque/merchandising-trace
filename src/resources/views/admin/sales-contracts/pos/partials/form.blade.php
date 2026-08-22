{{-- props: salesContract, salesContractPo (optional, for edit), stylesOptions, productTypesOptions, colorsOptions, washTypesOptions, shipModesOptions, sizesOptions --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Style <span class="text-danger">*</span></label>
        <select name="style_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($stylesOptions as $s)
                <option value="{{ $s->id }}" @selected(old('style_id', $salesContractPo->style_id ?? '') == $s->id)>{{ $s->style_no }} — {{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Color <span class="text-danger">*</span></label>
        <select name="color_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($colorsOptions as $c)
                <option value="{{ $c->id }}" @selected(old('color_id', $salesContractPo->color_id ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Product Type</label>
        <select name="product_type_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($productTypesOptions as $pt)
                <option value="{{ $pt->id }}" @selected(old('product_type_id', $salesContractPo->product_type_id ?? '') == $pt->id)>{{ $pt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Wash Type</label>
        <select name="wash_type_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($washTypesOptions as $w)
                <option value="{{ $w->id }}" @selected(old('wash_type_id', $salesContractPo->wash_type_id ?? '') == $w->id)>{{ $w->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">PO No <span class="text-danger">*</span></label>
        <input type="text" name="po_no" class="form-control" value="{{ old('po_no', $salesContractPo->po_no ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">PO Due Date</label>
        <input type="date" name="po_due_date" class="form-control" value="{{ old('po_due_date', optional($salesContractPo->po_due_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">PO Qty <span class="text-danger">*</span></label>
        <input type="number" min="0" name="po_qty" id="poQtyInput" class="form-control" value="{{ old('po_qty', $salesContractPo->po_qty ?? 0) }}" required {{ isset($salesContractPo) ? 'readonly' : '' }}>
        @if(isset($salesContractPo))<span class="form-text">Base qty is locked — use "Revise Qty" below to log a revision.</span>@endif
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Unit Price</label>
        <input type="number" step="0.0001" min="0" name="unit_price" class="form-control" value="{{ old('unit_price', $salesContractPo->unit_price ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Price Type</label>
        <select name="price_type" class="form-control">
            <option value="">— Select —</option>
            @foreach(['FOB', 'CM', 'CMT', 'CIF', 'DDP', 'FOC'] as $pt)
                <option value="{{ $pt }}" @selected(old('price_type', $salesContractPo->price_type ?? '') === $pt)>{{ $pt }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Ship Mode</label>
        <select name="ship_mode_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($shipModesOptions as $sm)
                <option value="{{ $sm->id }}" @selected(old('ship_mode_id', $salesContractPo->ship_mode_id ?? '') == $sm->id)>{{ $sm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Cost SMV</label>
        <input type="number" step="0.01" min="0" name="cost_smv" class="form-control" value="{{ old('cost_smv', $salesContractPo->cost_smv ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">CM</label>
        <input type="number" step="0.0001" min="0" name="cm" class="form-control" value="{{ old('cm', $salesContractPo->cm ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">FOB/FOC</label>
        <input type="number" step="0.0001" min="0" name="fob_foc" class="form-control" value="{{ old('fob_foc', $salesContractPo->fob_foc ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">PCD {{ isset($salesContractPo) ? '(base — see revisions below)' : '' }}</label>
        <input type="date" name="pcd_date" class="form-control" value="{{ old('pcd_date', optional($salesContractPo->pcd_date ?? null)->format('Y-m-d')) }}" {{ isset($salesContractPo) ? 'readonly' : '' }}>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Shipment Date {{ isset($salesContractPo) ? '(base — see revisions below)' : '' }}</label>
        <input type="date" name="shipment_date" class="form-control" value="{{ old('shipment_date', optional($salesContractPo->shipment_date ?? null)->format('Y-m-d')) }}" {{ isset($salesContractPo) ? 'readonly' : '' }}>
    </div>
</div>

<hr>
<h6>Embellishment</h6>
<div class="row">
    @foreach(['print_emb' => 'Print / Emb', 'emb_applique_ih' => 'EMB Applique IH', 'studs_stones_ih' => 'Studs/Stones IH', 'heat_seal_ih' => 'Heat Seal IH'] as $field => $label)
        <div class="col-md-3 mb-3">
            <label class="form-label">{{ $label }}</label>
            <select name="{{ $field }}" class="form-control">
                @foreach(['na' => 'N/A', 'yes' => 'Yes', 'no' => 'No'] as $val => $l)
                    <option value="{{ $val }}" @selected(old($field, $salesContractPo->{$field} ?? 'na') === $val)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
</div>

<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">Size Breakdown</h6>
    <span class="text-muted">Total: <strong id="sizeQtyTotal">0</strong></span>
</div>
<div class="row">
    @php $sizeQtyByIndex = old('sizes', isset($salesContractPo) ? $salesContractPo->sizes->mapWithKeys(fn ($s) => [$s->size_id => $s->qty])->all() : []); @endphp
    @foreach($sizesOptions as $size)
        <div class="col-md-2 mb-2">
            <label class="form-label">{{ $size->name }}</label>
            <input type="hidden" name="sizes[{{ $loop->index }}][size_id]" value="{{ $size->id }}">
            <input type="number" min="0" name="sizes[{{ $loop->index }}][qty]" class="form-control size-qty-input"
                value="{{ is_array($sizeQtyByIndex) && isset($sizeQtyByIndex[$size->id]) ? $sizeQtyByIndex[$size->id] : (old('sizes.' . $loop->index . '.qty') ?? 0) }}">
        </div>
    @endforeach
</div>

@if(isset($salesContractPo))
    <hr>
    <h6>Revise Qty / PCD / Shipment</h6>
    <div class="alert alert-secondary">Base values are locked once created — use these to record a revision with a reason (§ effective-value rule: downstream modules always read the latest revision).</div>
    <div class="row">
        @foreach([['field' => 'po_qty', 'label' => 'PO Qty', 'type' => 'number', 'current' => $salesContractPo->effectiveQty()], ['field' => 'pcd', 'label' => 'PCD', 'type' => 'date', 'current' => optional($salesContractPo->effectivePcd())->format('Y-m-d')], ['field' => 'shipment', 'label' => 'Shipment', 'type' => 'date', 'current' => optional($salesContractPo->effectiveShipment())->format('Y-m-d')]] as $rev)
            <div class="col-md-4 mb-3 border rounded p-2">
                <div class="fw-bold">{{ $rev['label'] }} <span class="text-muted small">(current: {{ $rev['current'] ?? '-' }})</span></div>
                <input type="{{ $rev['type'] }}" class="form-control mt-1 mb-1" id="revise_{{ $rev['field'] }}_value">
                <input type="text" class="form-control mb-1" id="revise_{{ $rev['field'] }}_reason" placeholder="Reason (required)">
                <button type="button" class="btn btn-sm btn-outline-warning revise-btn" data-field="{{ $rev['field'] }}">Log Revision</button>
            </div>
        @endforeach
    </div>
@endif

@push('js')
<script>
    (function () {
        function recomputeSizeTotal() {
            let total = 0;
            document.querySelectorAll('.size-qty-input').forEach(function (el) { total += parseInt(el.value || 0, 10); });
            document.getElementById('sizeQtyTotal').textContent = total;
            const poQty = document.getElementById('poQtyInput');
            if (poQty && !poQty.readOnly) { poQty.value = total; }
        }
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('size-qty-input')) { recomputeSizeTotal(); }
        });
        recomputeSizeTotal();

        document.querySelectorAll('.revise-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const field = btn.dataset.field;
                const value = document.getElementById('revise_' + field + '_value').value;
                const reason = document.getElementById('revise_' + field + '_reason').value;
                if (!value || !reason) { alert('Value and reason are both required.'); return; }
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ isset($salesContractPo) ? route("merchandising-trace.sales-contracts.pos.revise", [$salesContract, $salesContractPo]) : "#" }}';
                form.innerHTML = '@csrf'
                    + '<input type="hidden" name="field" value="' + field + '">'
                    + '<input type="hidden" name="value" value="' + value + '">'
                    + '<input type="hidden" name="reason" value="' + reason + '">';
                document.body.appendChild(form);
                form.submit();
            });
        });
    })();
</script>
@endpush
