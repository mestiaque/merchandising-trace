<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StylePart;

class StylePartController extends Controller
{
    public function store(Request $request, Style $style): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        $data = $request->validate([
            'trc_part_id' => ['required', 'integer'],
            'qty_per_garment' => ['required', 'integer', 'min:1'],
            'embellishment_type' => ['required', 'string', 'in:' . implode(',', StylePart::EMBELLISHMENT_TYPES)],
            'placement' => ['nullable', 'string', 'max:150'],
            'is_critical' => ['nullable', 'boolean'],
        ]);

        $style->parts()->updateOrCreate(['trc_part_id' => $data['trc_part_id']], $data);

        return back()->with('success', 'Part saved.');
    }

    public function destroy(Style $style, StylePart $part): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        abort_unless($part->style_id === $style->id, 404);
        $part->delete();

        return back()->with('success', 'Part removed.');
    }
}
