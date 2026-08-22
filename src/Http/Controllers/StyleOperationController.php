<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StyleOperation;

class StyleOperationController extends Controller
{
    public function store(Request $request, Style $style): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        $data = $request->validate([
            'operation_name' => ['required', 'string', 'max:150'],
            'machine_type' => ['nullable', 'string', 'max:100'],
            'smv' => ['required', 'numeric', 'min:0'],
        ]);

        $data['sequence'] = $style->operations()->count() + 1;
        $style->operations()->create($data);

        return back()->with('success', 'Operation added.');
    }

    public function destroy(Style $style, StyleOperation $operation): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        abort_unless($operation->style_id === $style->id, 404);
        $operation->delete();

        return back()->with('success', 'Operation removed.');
    }
}
