<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StyleMeasurement;

class StyleMeasurementController extends Controller
{
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
