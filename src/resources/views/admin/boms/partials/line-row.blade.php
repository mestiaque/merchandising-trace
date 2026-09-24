{{-- props: index (int|'__INDEX__'), line (array), itemsOptions, colorsOptions, sizesOptions, uomsOptions, suppliersOptions --}}
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
            @foreach($colorsOptions as $color)
                <option value="{{ $color->id }}" @selected(($line['color_id'] ?? null) == $color->id)>{{ $color->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="items[{{ $index }}][size_id]" class="form-control merch-select2">
            <option value="">—</option>
            @foreach($sizesOptions as $size)
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
