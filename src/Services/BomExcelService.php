<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\Uom;

/**
 * §8 deliverable 5: "Excel import/export templates for ... BOM." Import
 * ADDS lines to the BOM's existing items (never replaces) -- the same
 * bulk-populate use case as a freshly created BOM, matching how
 * SalesContractPoImportService adds PO lines rather than resetting the
 * contract.
 */
class BomExcelService
{
    public const HEADERS = [
        'Item Code', 'Part Name', 'Color Code', 'Size Name', 'Consumption',
        'UOM Code', 'Wastage %', 'Rate', 'Supplier Code', 'Lead Time Days',
    ];

    public function export(Bom $bom): array
    {
        $rows = $bom->items->map(fn ($line) => [
            'Item Code' => $line->item->code ?? '',
            'Part Name' => $line->part_name,
            'Color Code' => $line->color->code ?? '',
            'Size Name' => $line->size->name ?? '',
            'Consumption' => $line->consumption,
            'UOM Code' => $line->uom->code ?? '',
            'Wastage %' => $line->wastage_percent,
            'Rate' => $line->rate,
            'Supplier Code' => $line->supplier->code ?? '',
            'Lead Time Days' => $line->lead_time_days,
        ])->all();

        return ['headers' => self::HEADERS, 'rows' => $rows];
    }

    public function import(Bom $bom, array $sheet): array
    {
        $header = array_shift($sheet) ?? [];
        $itemCol = array_search('Item Code', $header, true);

        if ($itemCol === false) {
            throw new \RuntimeException('Column "Item Code" not found in the uploaded file.');
        }

        $col = fn (string $label) => array_search($label, $header, true);
        $consumptionCol = $col('Consumption');
        $partNameCol = $col('Part Name');
        $colorCol = $col('Color Code');
        $sizeCol = $col('Size Name');
        $uomCol = $col('UOM Code');
        $wastageCol = $col('Wastage %');
        $rateCol = $col('Rate');
        $supplierCol = $col('Supplier Code');
        $leadTimeCol = $col('Lead Time Days');

        $created = 0;
        $skipped = [];

        foreach ($sheet as $i => $row) {
            $itemCode = $row[$itemCol] ?? null;
            if (! $itemCode) {
                continue;
            }

            $item = Item::where('code', $itemCode)->first();
            if (! $item) {
                $skipped[] = 'Row ' . ($i + 2) . ": item not found ({$itemCode})";
                continue;
            }

            $color = $colorCol !== false && ! empty($row[$colorCol]) ? Color::where('code', $row[$colorCol])->first() : null;
            $size = $sizeCol !== false && ! empty($row[$sizeCol]) ? Size::where('name', $row[$sizeCol])->first() : null;
            $uom = $uomCol !== false && ! empty($row[$uomCol]) ? Uom::where('code', $row[$uomCol])->first() : null;
            $supplier = $supplierCol !== false && ! empty($row[$supplierCol]) ? Supplier::where('code', $row[$supplierCol])->first() : null;

            $bom->items()->create([
                'item_id' => $item->id,
                'item_type' => $item->type,
                'color_id' => $color?->id,
                'size_id' => $size?->id,
                'part_name' => $partNameCol !== false ? ($row[$partNameCol] ?? null) : null,
                'consumption' => $consumptionCol !== false && $row[$consumptionCol] !== '' ? $row[$consumptionCol] : 0,
                'uom_id' => $uom?->id ?? $item->uom_id,
                'wastage_percent' => $wastageCol !== false && $row[$wastageCol] !== '' ? $row[$wastageCol] : 0,
                'rate' => $rateCol !== false && $row[$rateCol] !== '' ? $row[$rateCol] : $item->default_price,
                'supplier_id' => $supplier?->id ?? $item->default_supplier_id,
                'lead_time_days' => $leadTimeCol !== false && $row[$leadTimeCol] !== '' ? $row[$leadTimeCol] : null,
            ]);
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}
