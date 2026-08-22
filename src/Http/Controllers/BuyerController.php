<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\BuyerRequest;
use ME\MerchandisingTrace\Models\Buyer;

class BuyerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_buyer.list');

        $buyers = Buyer::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.buyers.index', [
            'buyers' => $buyers,
            'merchandisersOptions' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(BuyerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Buyer::create($data);

        return back()->with('success', 'Buyer created successfully.');
    }

    public function update(BuyerRequest $request, Buyer $buyer): RedirectResponse
    {
        $buyer->update($request->validated());

        return back()->with('success', 'Buyer updated successfully.');
    }

    public function destroy(Buyer $buyer): RedirectResponse
    {
        $this->authorize('merch_buyer.delete');

        $buyer->delete();

        return back()->with('success', 'Buyer deleted successfully.');
    }
}
