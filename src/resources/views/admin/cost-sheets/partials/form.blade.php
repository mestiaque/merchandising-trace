{{-- props: costSheet (optional, for edit), stylesOptions, buyersOptions, currenciesOptions, itemsOptions, uomsOptions --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Style <span class="text-danger">*</span></label>
        <select name="style_id" class="form-control merch-select2" required {{ isset($costSheet) ? 'disabled' : '' }}>
            <option value="">— Select —</option>
            @foreach($stylesOptions as $style)
                <option value="{{ $style->id }}" @selected(old('style_id', $costSheet->style_id ?? '') == $style->id)>{{ $style->style_no }} — {{ $style->name }}</option>
            @endforeach
        </select>
        @if(isset($costSheet))<input type="hidden" name="style_id" value="{{ $costSheet->style_id }}">@endif
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Buyer <span class="text-danger">*</span></label>
        <select name="buyer_id" class="form-control merch-select2" required {{ isset($costSheet) ? 'disabled' : '' }}>
            <option value="">— Select —</option>
            @foreach($buyersOptions as $buyer)
                <option value="{{ $buyer->id }}" @selected(old('buyer_id', $costSheet->buyer_id ?? '') == $buyer->id)>{{ $buyer->name }}</option>
            @endforeach
        </select>
        @if(isset($costSheet))<input type="hidden" name="buyer_id" value="{{ $costSheet->buyer_id }}">@endif
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Currency</label>
        <select name="currency_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($currenciesOptions as $c)
                <option value="{{ $c->id }}" @selected(old('currency_id', $costSheet->currency_id ?? '') == $c->id)>{{ $c->code }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Order Qty</label>
        <input type="number" min="0" name="order_qty" class="form-control" value="{{ old('order_qty', $costSheet->order_qty ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">SMV</label>
        <input type="number" step="0.01" min="0" name="smv" class="form-control what-if-input" value="{{ old('smv', $costSheet->smv ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">CM Minute Rate</label>
        <input type="number" step="0.0001" min="0" name="cm_minute_rate" class="form-control what-if-input" value="{{ old('cm_minute_rate', $costSheet->cm_minute_rate ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Efficiency %</label>
        <input type="number" step="0.01" min="1" max="200" name="efficiency_percent" class="form-control what-if-input" value="{{ old('efficiency_percent', $costSheet->efficiency_percent ?? 100) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Print/Emb Cost</label>
        <input type="number" step="0.0001" min="0" name="print_emb_cost" class="form-control what-if-input" value="{{ old('print_emb_cost', $costSheet->print_emb_cost ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Wash Cost</label>
        <input type="number" step="0.0001" min="0" name="wash_cost" class="form-control what-if-input" value="{{ old('wash_cost', $costSheet->wash_cost ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Freight Cost</label>
        <input type="number" step="0.0001" min="0" name="freight_cost" class="form-control what-if-input" value="{{ old('freight_cost', $costSheet->freight_cost ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Testing Cost</label>
        <input type="number" step="0.0001" min="0" name="testing_cost" class="form-control what-if-input" value="{{ old('testing_cost', $costSheet->testing_cost ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Overhead Cost</label>
        <input type="number" step="0.0001" min="0" name="overhead_cost" class="form-control what-if-input" value="{{ old('overhead_cost', $costSheet->overhead_cost ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Profit %</label>
        <input type="number" step="0.01" min="0" max="99" name="profit_percent" class="form-control what-if-input" value="{{ old('profit_percent', $costSheet->profit_percent ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Price Type</label>
        <select name="price_type" class="form-control">
            @foreach(\ME\MerchandisingTrace\Models\CostSheet::PRICE_TYPES as $pt)
                <option value="{{ $pt }}" @selected(old('price_type', $costSheet->price_type ?? 'FOB') === $pt)>{{ $pt }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Buyer Target Price</label>
        <input type="number" step="0.0001" min="0" name="buyer_target_price" class="form-control" value="{{ old('buyer_target_price', $costSheet->buyer_target_price ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Final Price</label>
        <input type="number" step="0.0001" min="0" name="final_price" class="form-control" value="{{ old('final_price', $costSheet->final_price ?? '') }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $costSheet->remarks ?? '') }}</textarea>
    </div>
</div>

<div class="alert alert-info">
    <strong>Live estimate</strong> (recalculated server-side on save — this is a rough client preview):
    CM ≈ <span id="whatIfCm">0.00</span> · Total Cost ≈ <span id="whatIfTotal">0.00</span> · Offer Price ≈ <span id="whatIfOffer">0.00</span>
</div>

<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">Cost Lines (Fabric / Trims / Accessories / Process / Commercial)</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addCostItemBtn"><i class="fa-solid fa-plus"></i> Add Row</button>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead>
            <tr><th style="min-width:130px">Group</th><th style="min-width:180px">Item</th><th style="min-width:160px">Description</th><th style="width:110px">Consumption</th><th style="min-width:120px">UOM</th><th style="width:110px">Rate</th><th style="width:40px"></th></tr>
        </thead>
        <tbody id="costRowsBody">
            @php $lines = old('items', isset($costSheet) ? $costSheet->items->map(fn ($i) => $i->toArray())->all() : [[]]); @endphp
            @foreach($lines as $index => $line)
                <tr>
                    <td>
                        <select name="items[{{ $index }}][group]" class="form-control">
                            @foreach(\ME\MerchandisingTrace\Models\CostSheetItem::GROUPS as $g)
                                <option value="{{ $g }}" @selected(($line['group'] ?? '') === $g)>{{ ucfirst($g) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="items[{{ $index }}][item_id]" class="form-control merch-select2">
                            <option value="">— Select —</option>
                            @foreach($itemsOptions as $item)
                                <option value="{{ $item->id }}" @selected(($line['item_id'] ?? null) == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="items[{{ $index }}][description]" class="form-control" value="{{ $line['description'] ?? '' }}"></td>
                    <td><input type="number" step="0.0001" min="0" name="items[{{ $index }}][consumption]" class="form-control" value="{{ $line['consumption'] ?? '' }}"></td>
                    <td>
                        <select name="items[{{ $index }}][uom_id]" class="form-control merch-select2">
                            <option value="">—</option>
                            @foreach($uomsOptions as $uom)
                                <option value="{{ $uom->id }}" @selected(($line['uom_id'] ?? null) == $uom->id)>{{ $uom->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="0.0001" min="0" name="items[{{ $index }}][rate]" class="form-control" value="{{ $line['rate'] ?? '' }}"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-xmark"></i></button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<template id="costRowTemplate">
    <tr>
        <td>
            <select name="items[__INDEX__][group]" class="form-control">
                @foreach(\ME\MerchandisingTrace\Models\CostSheetItem::GROUPS as $g)
                    <option value="{{ $g }}">{{ ucfirst($g) }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="items[__INDEX__][item_id]" class="form-control merch-select2">
                <option value="">— Select —</option>
                @foreach($itemsOptions as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="items[__INDEX__][description]" class="form-control"></td>
        <td><input type="number" step="0.0001" min="0" name="items[__INDEX__][consumption]" class="form-control"></td>
        <td>
            <select name="items[__INDEX__][uom_id]" class="form-control merch-select2">
                <option value="">—</option>
                @foreach($uomsOptions as $uom)
                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="0.0001" min="0" name="items[__INDEX__][rate]" class="form-control"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-xmark"></i></button></td>
    </tr>
</template>

@push('js')
<script>
    (function () {
        let rowIndex = 1000000;
        document.getElementById('addCostItemBtn')?.addEventListener('click', function () {
            const tpl = document.getElementById('costRowTemplate');
            const body = document.getElementById('costRowsBody');
            const html = tpl.innerHTML.replaceAll('__INDEX__', rowIndex++);
            const wrap = document.createElement('table');
            wrap.innerHTML = '<tbody>' + html + '</tbody>';
            body.appendChild(wrap.querySelector('tr'));
            prodSelect2Init(document);
            recomputeWhatIf();
        });
        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-remove-row]')) {
                e.target.closest('tr').remove();
                recomputeWhatIf();
            }
        });

        function val(name) {
            const el = document.querySelector('[name="' + name + '"]');
            return el ? (parseFloat(el.value) || 0) : 0;
        }

        function recomputeWhatIf() {
            const smv = val('smv');
            const rate = val('cm_minute_rate');
            const eff = val('efficiency_percent') || 100;
            const cm = (smv && rate) ? (smv / (eff / 100)) * rate : 0;

            let lineTotal = 0;
            document.querySelectorAll('#costRowsBody tr').forEach(function (row) {
                const cons = parseFloat(row.querySelector('[name*="[consumption]"]')?.value || 0);
                const r = parseFloat(row.querySelector('[name*="[rate]"]')?.value || 0);
                lineTotal += cons * r;
            });

            const flat = val('print_emb_cost') + val('wash_cost') + val('freight_cost') + val('testing_cost') + val('overhead_cost');
            const total = lineTotal + cm + flat;
            const profitPct = val('profit_percent') / 100;
            const offer = profitPct < 1 ? total / (1 - profitPct) : 0;

            document.getElementById('whatIfCm').textContent = cm.toFixed(4);
            document.getElementById('whatIfTotal').textContent = total.toFixed(4);
            document.getElementById('whatIfOffer').textContent = offer.toFixed(4);
        }

        document.addEventListener('input', recomputeWhatIf);
        recomputeWhatIf();
    })();
</script>
@endpush
