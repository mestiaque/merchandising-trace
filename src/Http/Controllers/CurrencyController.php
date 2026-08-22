<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\CurrencyRequest;
use ME\MerchandisingTrace\Models\Currency;

class CurrencyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_currency.list');

        $currencies = Currency::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%'))
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.currencies.index', ['currencies' => $currencies]);
    }

    public function store(CurrencyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Currency::create($data);

        return back()->with('success', 'Currency created successfully.');
    }

    public function update(CurrencyRequest $request, Currency $currency): RedirectResponse
    {
        $currency->update($request->validated());

        return back()->with('success', 'Currency updated successfully.');
    }

    public function destroy(Currency $currency): RedirectResponse
    {
        $this->authorize('merch_currency.delete');

        $currency->delete();

        return back()->with('success', 'Currency deleted successfully.');
    }
}
