<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\StyleRequest;
use ME\MerchandisingTrace\Models\Brand;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Style;

class StyleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_style.list');

        $styles = Style::query()
            ->with(['buyer', 'brand'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('style_no', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.styles.index', ['styles' => $styles] + $this->formOptions());
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_style.list');

        $styles = Style::query()
            ->with(['buyer', 'brand'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('style_no', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Styles',
            'columns' => ['style_no' => 'Style No', 'name' => 'Name', 'buyer.name' => 'Buyer', 'brand.name' => 'Brand'],
            'rows'    => $styles,
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
            'brandsOptions' => Brand::query()->active()->orderBy('name')->get(),
        ];
    }
}
