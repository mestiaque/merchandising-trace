<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SalesContractRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\Factory;
use ME\MerchandisingTrace\Models\Inquiry;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class SalesContractController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_sales_contract.list');

        $salesContracts = SalesContract::query()
            ->with(['buyer'])
            ->when($request->filled('search'), fn ($q) => $q->where('contract_no', 'like', '%' . $request->search . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.sales-contracts.index', ['salesContracts' => $salesContracts]);
    }

    public function create(): View
    {
        $this->authorize('merch_sales_contract.add');

        return view('merchandising-trace::admin.sales-contracts.create', $this->formOptions());
    }

    public function store(SalesContractRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();
        $data['contract_no'] = $numbers->next(SalesContract::class, 'contract_no', 'SC');
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();

        $salesContract = SalesContract::create($data);

        return redirect()->route('merchandising-trace.sales-contracts.show', $salesContract)->with('success', "Sales Contract {$salesContract->contract_no} created successfully.");
    }

    public function show(SalesContract $salesContract): View
    {
        $this->authorize('merch_sales_contract.view');

        $salesContract->load(['buyer', 'season', 'merchandiser', 'factory', 'pos.style', 'pos.color', 'pos.sizes.size']);

        return view('merchandising-trace::admin.sales-contracts.show', ['salesContract' => $salesContract]);
    }

    public function edit(SalesContract $salesContract): View
    {
        $this->authorize('merch_sales_contract.edit');

        return view('merchandising-trace::admin.sales-contracts.edit', ['salesContract' => $salesContract] + $this->formOptions());
    }

    public function update(SalesContractRequest $request, SalesContract $salesContract): RedirectResponse
    {
        $salesContract->update($request->validated());

        return redirect()->route('merchandising-trace.sales-contracts.show', $salesContract)->with('success', 'Sales Contract updated successfully.');
    }

    public function destroy(SalesContract $salesContract): RedirectResponse
    {
        $this->authorize('merch_sales_contract.delete');

        $salesContract->delete();

        return redirect()->route('merchandising-trace.sales-contracts.index')->with('success', 'Sales Contract deleted successfully.');
    }

    /**
     * §M07 AC: confirming a contract auto-generates the T&A plan for every
     * PO row. The T&A plan-generation call is wired once P7/P8 exist.
     */
    public function confirm(SalesContract $salesContract): RedirectResponse
    {
        $this->authorize('merch_sales_contract.edit');

        $salesContract->update(['status' => 'confirmed']);
        $salesContract->pos()->update(['status' => 'tna_created']);

        return back()->with('success', "Sales Contract {$salesContract->contract_no} confirmed.");
    }

    private function formOptions(): array
    {
        return [
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'seasonsOptions' => Season::query()->active()->orderBy('name')->get(),
            'merchandisersOptions' => User::query()->orderBy('name')->get(),
            'factoriesOptions' => Factory::query()->active()->orderBy('name')->get(),
            'inquiriesOptions' => Inquiry::query()->orderBy('inquiry_no', 'desc')->limit(200)->get(),
            'currenciesOptions' => Currency::query()->active()->orderBy('code')->get(),
        ];
    }
}
