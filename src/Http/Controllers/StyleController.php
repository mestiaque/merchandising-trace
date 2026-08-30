<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\StyleRequest;
use ME\MerchandisingTrace\Models\Bridge\TrcPart;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\ProductType;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StyleImageType;
use ME\MerchandisingTrace\Models\WashType;

class StyleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_style.list');

        $styles = Style::query()
            ->with(['buyer', 'season'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%')->orWhere('style_no', 'like', '%' . $request->search . '%'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.styles.index', ['styles' => $styles] + $this->formOptions());
    }

    /**
     * §M03 — the tabbed detail page (Basic | Images | Measurement Chart |
     * Parts & Embellishment | Operations/SMV).
     */
    public function show(Style $style): View
    {
        $this->authorize('merch_style.view');

        $style->load(['images', 'measurements.sizes', 'parts', 'operations', 'buyer', 'season']);

        return view('merchandising-trace::admin.styles.show', [
            'style' => $style,
            'trcPartsOptions' => TrcPart::query()->active()->orderBy('name')->get(),
            'sizesOptions' => Size::query()->active()->orderBy('id')->get(),
            'imageTypesOptions' => StyleImageType::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StyleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Style::create($data);

        return back()->with('success', 'Style created successfully.');
    }

    public function update(StyleRequest $request, Style $style): RedirectResponse
    {
        $style->update($request->validated());

        return back()->with('success', 'Style updated successfully.');
    }

    public function destroy(Style $style): RedirectResponse
    {
        $this->authorize('merch_style.delete');

        $style->delete();

        return back()->with('success', 'Style deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'seasonsOptions' => Season::query()->active()->orderBy('name')->get(),
            'merchandisersOptions' => User::query()->orderBy('name')->get(),
            'washTypesOptions' => WashType::query()->active()->orderBy('name')->get(),
            'productTypesOptions' => ProductType::query()->active()->orderBy('name')->get(),
        ];
    }
}
