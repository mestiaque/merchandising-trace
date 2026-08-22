<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;

/**
 * §8 deliverable 5: "Excel import/export templates for ... measurement
 * chart." POM rows x size columns, matching the Measurement Chart tab
 * (§M03) exactly. Import upserts by `pom_code` within the style -- a
 * re-import of an edited sheet updates the same rows rather than
 * duplicating them.
 */
class MeasurementChartExcelService
{
    public function export(Style $style): array
    {
        $sizes = Size::query()->active()->orderBy('id')->get();
        $headers = array_merge(['POM Code', 'POM Name', 'Tol +', 'Tol -'], $sizes->pluck('name')->all());

        $rows = $style->measurements->map(function ($m) use ($sizes) {
            $row = ['POM Code' => $m->pom_code, 'POM Name' => $m->pom_name, 'Tol +' => $m->tolerance_plus, 'Tol -' => $m->tolerance_minus];
            $valuesBySize = $m->sizes->keyBy('size_id');
            foreach ($sizes as $size) {
                $row[$size->name] = $valuesBySize->get($size->id)?->value;
            }

            return $row;
        })->all();

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function import(Style $style, array $sheet): array
    {
        $header = array_shift($sheet) ?? [];
        $pomCodeCol = array_search('POM Code', $header, true);

        if ($pomCodeCol === false) {
            throw new \RuntimeException('Column "POM Code" not found in the uploaded file.');
        }

        $pomNameCol = array_search('POM Name', $header, true);
        $tolPlusCol = array_search('Tol +', $header, true);
        $tolMinusCol = array_search('Tol -', $header, true);

        $sizesByName = Size::query()->active()->get()->keyBy('name');
        $sizeCols = [];
        foreach ($header as $col => $label) {
            if ($sizesByName->has($label)) {
                $sizeCols[$col] = $sizesByName->get($label)->id;
            }
        }

        $created = 0;
        $updated = 0;

        foreach ($sheet as $row) {
            $pomCode = $row[$pomCodeCol] ?? null;
            if (! $pomCode) {
                continue;
            }

            $existing = $style->measurements()->where('pom_code', $pomCode)->first();

            $measurement = $style->measurements()->updateOrCreate(
                ['pom_code' => $pomCode],
                [
                    'pom_name' => $pomNameCol !== false ? ($row[$pomNameCol] ?? $pomCode) : $pomCode,
                    'tolerance_plus' => $tolPlusCol !== false && $row[$tolPlusCol] !== '' ? $row[$tolPlusCol] : null,
                    'tolerance_minus' => $tolMinusCol !== false && $row[$tolMinusCol] !== '' ? $row[$tolMinusCol] : null,
                    'sort_order' => $existing?->sort_order ?? ($style->measurements()->count() + 1),
                ]
            );

            $existing ? $updated++ : $created++;

            foreach ($sizeCols as $col => $sizeId) {
                $value = $row[$col] ?? '';
                if ($value === '') {
                    continue;
                }
                $measurement->sizes()->updateOrCreate(['size_id' => $sizeId], ['value' => $value]);
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }
}
