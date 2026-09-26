{{-- props: index, group, line (array), dozenUomId, itemsOptions, uomsOptions, suppliersOptions (id => name) --}}
<tr>
    <td class="text-center" data-sl></td>
    <td>
        <input type="hidden" name="items[{{ $index }}][group]" value="{{ $group }}">
        <select name="items[{{ $index }}][item_id]" class="form-control form-control-sm merch-select2">
            <option value="">—</option>
            @foreach($itemsOptions as $item)
                <option value="{{ $item->id }}" @selected(($line['item_id'] ?? null) == $item->id)
                    data-name="{{ $item->name }}" data-uom="{{ $item->uom_id }}" data-price="{{ $item->default_price !== null ? (float) $item->default_price : '' }}"
                    data-supplier="{{ $suppliersOptions[$item->default_supplier_id] ?? '' }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="text" name="items[{{ $index }}][description]" class="form-control form-control-sm" maxlength="255" value="{{ $line['description'] ?? '' }}"></td>
    <td><input type="text" name="items[{{ $index }}][supplier_name]" class="form-control form-control-sm" maxlength="150" list="csSupplierList" value="{{ $line['supplier_name'] ?? '' }}"></td>
    <td><input type="number" step="0.0001" min="0" name="items[{{ $index }}][consumption]" class="form-control form-control-sm" value="{{ $line['consumption'] ?? '' }}"></td>
    <td>
        <select name="items[{{ $index }}][uom_id]" class="form-control form-control-sm merch-select2">
            <option value="">—</option>
            @foreach($uomsOptions as $uom)
                <option value="{{ $uom->id }}" @selected(($line['uom_id'] ?? ($group !== 'fabric' ? $dozenUomId : null)) == $uom->id)>{{ $uom->code ?? $uom->name }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="number" step="0.0001" min="0" name="items[{{ $index }}][rate]" class="form-control form-control-sm" value="{{ $line['rate'] ?? '' }}"></td>
    <td class="text-right" data-line-total>-</td>
    <td><button type="button" class="btn-custom danger" data-remove-row><i class="fa-solid fa-xmark"></i></button></td>
</tr>
