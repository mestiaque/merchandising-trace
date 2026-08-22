<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ME\MerchandisingTrace\Exports\GenericArrayExport;
use ME\MerchandisingTrace\Imports\TnaGridImport;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StyleMeasurement;
use ME\MerchandisingTrace\Services\MeasurementChartExcelService;

class StyleMeasurementController extends Controller
{
    public function exportExcel(Style $style, MeasurementChartExcelService $service)
    {
        $this->authorize('merch_style.view');

        $style->load('measurements.sizes');
        $data = $service->export($style);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new GenericArrayExport($data['headers'], $data['rows']),
            "{$style->style_no}-measurement-chart.xlsx"
        );
    }

    public function importExcel(Request $request, Style $style, MeasurementChartExcelService $service): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new TnaGridImport(), $request->file('file'));

        try {
            $result = $service->import($style, $sheets[0] ?? []);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Measurement chart imported: {$result['created']} row(s) created, {$result['updated']} updated.");
    }

    public function store(Request $request, Style $style): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        $data = $request->validate([
            'pom_code' => ['required', 'string', 'max:30'],
            'pom_name' => ['required', 'string', 'max:150'],
            'tolerance_plus' => ['nullable', 'numeric'],
            'tolerance_minus' => ['nullable', 'numeric'],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'numeric'],
        ]);

        $measurement = $style->measurements()->create([
            'pom_code' => $data['pom_code'],
            'pom_name' => $data['pom_name'],
            'tolerance_plus' => $data['tolerance_plus'] ?? null,
            'tolerance_minus' => $data['tolerance_minus'] ?? null,
            'sort_order' => $style->measurements()->count() + 1,
        ]);

        foreach ($data['values'] ?? [] as $sizeId => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $measurement->sizes()->create(['size_id' => $sizeId, 'value' => $value]);
        }

        return back()->with('success', 'Measurement row added.');
    }

    public function destroy(Style $style, StyleMeasurement $measurement): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        abort_unless($measurement->style_id === $style->id, 404);
        $measurement->sizes()->delete();
        $measurement->delete();

        return back()->with('success', 'Measurement row removed.');
    }
}
