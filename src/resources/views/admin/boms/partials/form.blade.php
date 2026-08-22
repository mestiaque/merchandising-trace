{{-- props: bom (optional, for edit), stylesOptions, itemsOptions, uomsOptions, suppliersOptions --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Style <span class="text-danger">*</span></label>
        <select name="style_id" class="form-control merch-select2" required {{ isset($bom) ? 'disabled' : '' }}>
            <option value="">— Select —</option>
            @foreach($stylesOptions as $style)
                <option value="{{ $style->id }}" @selected(old('style_id', $bom->style_id ?? '') == $style->id)>{{ $style->style_no }} — {{ $style->name }}</option>
            @endforeach
        </select>
        @if(isset($bom))
            <input type="hidden" name="style_id" value="{{ $bom->style_id }}">
            <span class="form-text">A new version keeps the same style — create a fresh BOM to target a different style.</span>
        @endif
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $bom->remarks ?? '') }}</textarea>
    </div>
</div>

<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">BOM Lines</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addBomItemBtn"><i class="fa-solid fa-plus"></i> Add Row</button>
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
            @php $lines = old('items', isset($bom) ? $bom->items->map(fn ($i) => $i->toArray())->all() : [[]]); @endphp
            @foreach($lines as $index => $line)
                <tr>
                    <td>
                        <select name="items[{{ $index }}][item_id]" class="form-control merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($itemsOptions as $item)
                                <option value="{{ $item->id }}" @selected(($line['item_id'] ?? null) == $item->id)>{{ $item->name }} ({{ ucfirst($item->type) }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="items[{{ $index }}][color_id]" class="form-control merch-select2">
                            <option value="">—</option>
                            @foreach(($colorsOptions ?? []) as $color)
                                <option value="{{ $color->id }}" @selected(($line['color_id'] ?? null) == $color->id)>{{ $color->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="items[{{ $index }}][size_id]" class="form-control merch-select2">
                            <option value="">—</option>
                            @foreach(($sizesOptions ?? []) as $size)
                                <option value="{{ $size->id }}" @selected(($line['size_id'] ?? null) == $size->id)>{{ $size->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="items[{{ $index }}][part_name]" class="form-control" value="{{ $line['part_name'] ?? '' }}"></td>
                    <td><input type="number" step="0.0001" min="0" name="items[{{ $index }}][consumption]" class="form-control" value="{{ $line['consumption'] ?? '' }}" required></td>
                    <td>
                        <select name="items[{{ $index }}][uom_id]" class="form-control merch-select2">
                            <option value="">—</option>
                            @foreach($uomsOptions as $uom)
                                <option value="{{ $uom->id }}" @selected(($line['uom_id'] ?? null) == $uom->id)>{{ $uom->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="0.01" min="0" max="100" name="items[{{ $index }}][wastage_percent]" class="form-control" value="{{ $line['wastage_percent'] ?? 0 }}"></td>
                    <td><input type="number" step="0.0001" min="0" name="items[{{ $index }}][rate]" class="form-control" value="{{ $line['rate'] ?? '' }}"></td>
                    <td>
                        <select name="items[{{ $index }}][supplier_id]" class="form-control merch-select2">
                            <option value="">—</option>
                            @foreach($suppliersOptions as $s)
                                <option value="{{ $s->id }}" @selected(($line['supplier_id'] ?? null) == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-xmark"></i></button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<template id="bomRowTemplate">
    <tr>
        <td>
            <select name="items[__INDEX__][item_id]" class="form-control merch-select2" required>
                <option value="">— Select —</option>
                @foreach($itemsOptions as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} ({{ ucfirst($item->type) }})</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="items[__INDEX__][color_id]" class="form-control merch-select2">
                <option value="">—</option>
                @foreach(($colorsOptions ?? []) as $color)
                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="items[__INDEX__][size_id]" class="form-control merch-select2">
                <option value="">—</option>
                @foreach(($sizesOptions ?? []) as $size)
                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="items[__INDEX__][part_name]" class="form-control"></td>
        <td><input type="number" step="0.0001" min="0" name="items[__INDEX__][consumption]" class="form-control" required></td>
        <td>
            <select name="items[__INDEX__][uom_id]" class="form-control merch-select2">
                <option value="">—</option>
                @foreach($uomsOptions as $uom)
                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0" max="100" name="items[__INDEX__][wastage_percent]" class="form-control" value="0"></td>
        <td><input type="number" step="0.0001" min="0" name="items[__INDEX__][rate]" class="form-control"></td>
        <td>
            <select name="items[__INDEX__][supplier_id]" class="form-control merch-select2">
                <option value="">—</option>
                @foreach($suppliersOptions as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-xmark"></i></button></td>
    </tr>
</template>

@push('js')
<script>
    (function () {
        let rowIndex = 1000000;
        document.getElementById('addBomItemBtn')?.addEventListener('click', function () {
            const tpl = document.getElementById('bomRowTemplate');
            const body = document.getElementById('bomRowsBody');
            const html = tpl.innerHTML.replaceAll('__INDEX__', rowIndex++);
            const wrap = document.createElement('table');
            wrap.innerHTML = '<tbody>' + html + '</tbody>';
            body.appendChild(wrap.querySelector('tr'));
            prodSelect2Init(document);
        });
        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-remove-row]')) {
                e.target.closest('tr').remove();
            }
        });
    })();
</script>
@endpush
