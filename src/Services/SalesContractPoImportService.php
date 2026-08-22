<?php

namespace ME\MerchandisingTrace\Services;

use Illuminate\Support\Facades\DB;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\ShipMode;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\WashType;

/**
 * §M07 AC: "create/edit with a PO grid... Excel import." Header row +
 * one row per PO: Style No, Color Code, Wash Type Code, PO No, PO Qty,
 * PCD Date, Shipment Date, Unit Price, Ship Mode Code (last three
 * optional). Scoped to PO-level fields only -- the per-size breakdown is
 * still entered by hand afterwards, same as a freshly-added PO line.
 */
class SalesContractPoImportService
{
    private const REQUIRED_HEADERS = ['Style No', 'Color Code', 'PO No', 'PO Qty'];

    public function import(SalesContract $salesContract, array $sheet, int $userId): array
    {
        $header = array_shift($sheet) ?? [];
        $cols = [];
        foreach (self::REQUIRED_HEADERS as $required) {
            $pos = array_search($required, $header, true);
            if ($pos === false) {
                throw new \RuntimeException("Column \"{$required}\" not found in the uploaded file.");
            }
            $cols[$required] = $pos;
        }
        $optional = [
            'Wash Type Code' => array_search('Wash Type Code', $header, true),
            'PCD Date' => array_search('PCD Date', $header, true),
            'Shipment Date' => array_search('Shipment Date', $header, true),
            'Unit Price' => array_search('Unit Price', $header, true),
            'Ship Mode Code' => array_search('Ship Mode Code', $header, true),
        ];

        $created = 0;
        $skipped = [];

        DB::transaction(function () use ($sheet, $cols, $optional, $salesContract, &$created, &$skipped) {
            foreach ($sheet as $i => $row) {
                $styleNo = $row[$cols['Style No']] ?? null;
                $colorCode = $row[$cols['Color Code']] ?? null;
                $poNo = $row[$cols['PO No']] ?? null;
                $poQty = $row[$cols['PO Qty']] ?? null;

                if (! $styleNo || ! $colorCode || ! $poNo || ! $poQty) {
                    continue;
                }

                $style = Style::where('style_no', $styleNo)->first();
                $color = Color::where('code', $colorCode)->first();

                if (! $style || ! $color) {
                    $skipped[] = "Row " . ($i + 2) . ": style or color not found ({$styleNo}/{$colorCode})";
                    continue;
                }

                $washType = $optional['Wash Type Code'] !== false && ! empty($row[$optional['Wash Type Code']])
                    ? WashType::where('code', $row[$optional['Wash Type Code']])->first() : null;
                $shipMode = $optional['Ship Mode Code'] !== false && ! empty($row[$optional['Ship Mode Code']])
                    ? ShipMode::where('code', $row[$optional['Ship Mode Code']])->first() : null;

                $salesContract->pos()->create([
                    'style_id' => $style->id,
                    'color_id' => $color->id,
                    'wash_type_id' => $washType?->id,
                    'po_no' => $poNo,
                    'po_qty' => (int) $poQty,
                    'unit_price' => $optional['Unit Price'] !== false ? ($row[$optional['Unit Price']] ?? null) : null,
                    'pcd_date' => $optional['PCD Date'] !== false && ! empty($row[$optional['PCD Date']]) ? $row[$optional['PCD Date']] : null,
                    'shipment_date' => $optional['Shipment Date'] !== false && ! empty($row[$optional['Shipment Date']]) ? $row[$optional['Shipment Date']] : null,
                    'ship_mode_id' => $shipMode?->id,
                    'print_emb' => 'na',
                    'emb_applique_ih' => 'na',
                    'studs_stones_ih' => 'na',
                    'heat_seal_ih' => 'na',
                    'status' => 'pending',
                ]);
                $created++;
            }

            $salesContract->refreshTotals();
        });

        return ['created' => $created, 'skipped' => $skipped];
    }
}
