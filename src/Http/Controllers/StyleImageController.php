<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StyleImage;

class StyleImageController extends Controller
{
    public const TYPES = ['front', 'back', 'detail', 'embellishment', 'artwork'];

    public function store(Request $request, Style $style): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        $data = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', self::TYPES)],
            'caption' => ['nullable', 'string', 'max:150'],
            'file' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $path = $request->file('file')->store('merchandising-trace/style-images', 'public');

        $style->images()->create(['path' => $path, 'type' => $data['type'], 'caption' => $data['caption'] ?? null]);

        return back()->with('success', 'Image uploaded.');
    }

    public function destroy(Style $style, StyleImage $image): RedirectResponse
    {
        $this->authorize('merch_style.edit');

        abort_unless($image->style_id === $style->id, 404);
        $image->delete();

        return back()->with('success', 'Image removed.');
    }
}
