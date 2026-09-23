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

        $stats = [
            'total' => SalesContract::count(),
            'draft' => SalesContract::where('status', 'draft')->count(),
            'confirmed' => SalesContract::where('status', 'confirmed')->count(),
            'in_production' => SalesContract::where('status', 'in_production')->count(),
            'closed' => SalesContract::where('status', 'closed')->count(),
            'total_value' => (float) SalesContract::sum('total_value'),
        ];

        return view('merchandising-trace::admin.sales-contracts.index', ['salesContracts' => $salesContracts, 'stats' => $stats]);
    }

    public function create(): View
    {
        $this->authorize('merch_sales_contract.add');

        return view('merchandising-trace::admin.sales-contracts.create', $this->formOptions());
    }

    public function store(SalesContractRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();
        unset($data['files']);
        $data['contract_no'] = $numbers->next(SalesContract::class, 'contract_no', 'SC');
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();

        $salesContract = SalesContract::create($data);
        $this->storeFiles($request, $salesContract);

        return redirect()->route('merchandising-trace.sales-contracts.show', $salesContract)->with('success', "Sales Contract {$salesContract->contract_no} created successfully.");
    }

    public function show(SalesContract $salesContract): View
    {
        $this->authorize('merch_sales_contract.view');

        $salesContract->load(['buyer', 'season', 'merchandiser', 'factory', 'pos.style', 'pos.color', 'pos.sizes.size', 'files']);

        return view('merchandising-trace::admin.sales-contracts.show', ['salesContract' => $salesContract]);
    }

    public function edit(SalesContract $salesContract): View
    {
        $this->authorize('merch_sales_contract.edit');

        $salesContract->load('files');

        return view('merchandising-trace::admin.sales-contracts.edit', ['salesContract' => $salesContract] + $this->formOptions());
    }

    public function update(SalesContractRequest $request, SalesContract $salesContract): RedirectResponse
    {
        $data = $request->validated();
        unset($data['files']);
        $salesContract->update($data);
        $this->storeFiles($request, $salesContract);

        return redirect()->route('merchandising-trace.sales-contracts.show', $salesContract)->with('success', 'Sales Contract updated successfully.');
    }

    private function storeFiles(Request $request, SalesContract $salesContract): void
    {
        foreach ($request->file('files', []) as $file) {
            $path = $file->store('merchandising-trace/sales-contract-files', 'public');

            $salesContract->files()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    public function destroy(SalesContract $salesContract): RedirectResponse
    {
        $this->authorize('merch_sales_contract.delete');

        $salesContract->delete();

        return redirect()->route('merchandising-trace.sales-contracts.index')->with('success', 'Sales Contract deleted successfully.');
    }

    /**
     * §M07 AC: confirming a contract auto-generates the T&A plan for every
     * PO row from the buyer/product template.
     */
    public function confirm(SalesContract $salesContract, \ME\MerchandisingTrace\Services\TnaPlanGenerationService $tnaGenerator, \ME\MerchandisingTrace\Services\DocumentChecklistService $documents): RedirectResponse
    {
        $this->authorize('merch_sales_contract.edit');

        if ($salesContract->pos()->count() === 0) {
            return back()->with('error', 'Cannot confirm — add at least one PO/style line first.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($salesContract, $tnaGenerator, $documents) {
            $salesContract->update(['status' => 'confirmed']);

            foreach ($salesContract->pos as $po) {
                $tnaGenerator->generateFor($po);
                $po->update(['status' => 'tna_created']);
            }

            $documents->generateFor($salesContract);
        });

        return back()->with('success', "Sales Contract {$salesContract->contract_no} confirmed — T&A plans generated for " . $salesContract->pos()->count() . ' PO line(s).');
    }

    /**
     * §M13 AC: blocked while any mandatory document is missing.
     */
    public function close(SalesContract $salesContract): RedirectResponse
    {
        $this->authorize('merch_sales_contract.edit');

        if ($salesContract->hasOutstandingMandatoryDocuments()) {
            return back()->with('error', 'Cannot close — mandatory documents are still missing.');
        }

        $salesContract->update(['status' => 'closed']);

        return back()->with('success', "Sales Contract {$salesContract->contract_no} closed.");
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
