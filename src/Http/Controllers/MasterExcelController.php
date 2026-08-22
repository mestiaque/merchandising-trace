<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ME\MerchandisingTrace\Exports\GenericArrayExport;
use ME\MerchandisingTrace\Imports\TnaGridImport;

/**
 * §M01 AC: "Excel import/export" for every simple master. One controller
 * driven by config/master_excel.php's per-master column map instead of
 * duplicating export/import logic across all 14 master controllers.
 */
class MasterExcelController extends Controller
{
    public function export(string $master)
    {
        $def = $this->definition($master);
        $this->authorize($def['permPrefix'] . '.list');

        $rows = $def['model']::query()->get()->map(function ($row) use ($def) {
            $line = [];
            foreach ($def['columns'] as $label => $attribute) {
                $line[$label] = $row->{$attribute};
            }

            return $line;
        })->all();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new GenericArrayExport(array_keys($def['columns']), $rows),
            "{$master}.xlsx"
        );
    }

    public function import(Request $request, string $master): RedirectResponse
    {
        $def = $this->definition($master);
        $this->authorize($def['permPrefix'] . '.add');

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new TnaGridImport(), $request->file('file'));
        $sheet = $sheets[0] ?? [];
        $header = array_shift($sheet) ?? [];

        $uniqueLabel = array_search($def['unique'], $def['columns'], true);
        $uniqueCol = array_search($uniqueLabel, $header, true);

        if ($uniqueCol === false) {
            return back()->with('error', "Column \"{$uniqueLabel}\" not found in the uploaded file.");
        }

        $created = 0;
        $updated = 0;

        foreach ($sheet as $row) {
            $uniqueValue = $row[$uniqueCol] ?? null;
            if (! $uniqueValue) {
                continue;
            }

            $attributes = [];
            foreach ($def['columns'] as $label => $attribute) {
                $col = array_search($label, $header, true);
                if ($col === false || $col === $uniqueCol) {
                    continue;
                }
                if (($row[$col] ?? '') !== '') {
                    $attributes[$attribute] = $row[$col];
                }
            }

            $existing = $def['model']::where($def['unique'], $uniqueValue)->first();
            $def['model']::updateOrCreate([$def['unique'] => $uniqueValue], $attributes);
            $existing ? $updated++ : $created++;
        }

        return back()->with('success', "Import complete: {$created} created, {$updated} updated.");
    }

    private function definition(string $master): array
    {
        $def = config("merchandising-trace-master-excel.{$master}");
        abort_unless($def, 404);

        return $def;
    }
}
