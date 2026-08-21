{{-- props: bom (optional, for edit), stylesOptions, unitsOptions --}}
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
            <span class="form-text">Style can't be changed after creation — use "New Version" instead.</span>
        @endif
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Remarks</label>
        <input type="text" name="remarks" class="form-control" value="{{ old('remarks', $bom->remarks ?? '') }}">
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle" id="bomItemsTable">
        <thead>
            <tr>
                <th style="width:15%;">Item Type</th>
                <th>Material</th>
                <th style="width:15%;">Consumption</th>
                <th style="width:12%;">Waste %</th>
                <th style="width:15%;">Unit</th>
                <th>Remarks</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody id="bomItemsRowsBody">
            @php($existingItems = old('items', isset($bom) ? $bom->items->toArray() : [['item_type' => '', 'material_name' => '', 'consumption' => '', 'waste_percent' => '', 'unit_id' => '', 'remarks' => '']]))
            @foreach($existingItems as $index => $item)
                <tr>
                    <td>
                        <select name="items[{{ $index }}][item_type]" class="form-control" required>
                            @foreach(\ME\MerchandisingTrace\Models\BomItem::ITEM_TYPES as $type)
                                <option value="{{ $type }}" @selected(($item['item_type'] ?? '') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="items[{{ $index }}][material_name]" class="form-control" value="{{ $item['material_name'] ?? '' }}" required></td>
                    <td><input type="number" step="0.0001" name="items[{{ $index }}][consumption]" class="form-control" value="{{ $item['consumption'] ?? '' }}" required></td>
                    <td><input type="number" step="0.01" name="items[{{ $index }}][waste_percent]" class="form-control" value="{{ $item['waste_percent'] ?? 0 }}"></td>
                    <td>
                        <select name="items[{{ $index }}][unit_id]" class="form-control merch-select2">
                            <option value="">—</option>
                            @foreach($unitsOptions as $unit)
                                <option value="{{ $unit->id }}" @selected(($item['unit_id'] ?? '') == $unit->id)>{{ $unit->short_name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="items[{{ $index }}][remarks]" class="form-control" value="{{ $item['remarks'] ?? '' }}"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" data-line-items-remove><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-primary" data-line-items-add="bomItems"><i class="fa-solid fa-plus"></i> Add Line</button>
</div>

<template id="bomItemsRowTemplate">
    <tr>
        <td>
            <select name="items[__INDEX__][item_type]" class="form-control" required>
                @foreach(\ME\MerchandisingTrace\Models\BomItem::ITEM_TYPES as $type)
                    <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="items[__INDEX__][material_name]" class="form-control" required></td>
        <td><input type="number" step="0.0001" name="items[__INDEX__][consumption]" class="form-control" required></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][waste_percent]" class="form-control" value="0"></td>
        <td>
            <select name="items[__INDEX__][unit_id]" class="form-control merch-select2">
                <option value="">—</option>
                @foreach($unitsOptions as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->short_name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="items[__INDEX__][remarks]" class="form-control"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" data-line-items-remove><i class="fa-solid fa-trash"></i></button></td>
    </tr>
</template>
