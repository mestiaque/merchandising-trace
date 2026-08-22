<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\CostSheetRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\Uom;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class CostSheetController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_costing.list');

        $costSheets = CostSheet::query()
            ->with(['style', 'buyer'])
            ->when($request->filled('search'), fn ($q) => $q->where('cost_sheet_no', 'like', '%' . $request->search . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.cost-sheets.index', ['costSheets' => $costSheets]);
    }

    public function create(): View
    {
        $this->authorize('merch_costing.add');

        return view('merchandising-trace::admin.cost-sheets.create', $this->formOptions());
    }

    public function store(CostSheetRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();

        $costSheet = DB::transaction(function () use ($data, $numbers) {
            $version = CostSheet::where('style_id', $data['style_id'])->max('version');

            $costSheet = CostSheet::create([
                'cost_sheet_no' => $numbers->next(CostSheet::class, 'cost_sheet_no', 'CST'),
                'style_id' => $data['style_id'],
                'buyer_id' => $data['buyer_id'],
                'version' => ($version ?? 0) + 1,
                'currency_id' => $data['currency_id'] ?? null,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'order_qty' => $data['order_qty'] ?? null,
                'smv' => $data['smv'] ?? null,
                'cm_minute_rate' => $data['cm_minute_rate'] ?? null,
                'efficiency_percent' => $data['efficiency_percent'] ?? 100,
                'print_emb_cost' => $data['print_emb_cost'] ?? 0,
                'wash_cost' => $data['wash_cost'] ?? 0,
                'freight_cost' => $data['freight_cost'] ?? 0,
                'testing_cost' => $data['testing_cost'] ?? 0,
                'overhead_cost' => $data['overhead_cost'] ?? 0,
                'profit_percent' => $data['profit_percent'] ?? 0,
                'buyer_target_price' => $data['buyer_target_price'] ?? null,
                'final_price' => $data['final_price'] ?? null,
                'price_type' => $data['price_type'],
                'status' => 'draft',
                'prepared_by' => auth()->id(),
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] ?? [] as $line) {
                if (empty($line['description']) && empty($line['item_id'])) {
                    continue;
                }
                $costSheet->items()->create($line);
            }

            $costSheet->recompute();

            return $costSheet;
        });

        return redirect()->route('merchandising-trace.cost-sheets.show', $costSheet)->with('success', "Cost Sheet {$costSheet->cost_sheet_no} created successfully.");
    }

    public function show(CostSheet $costSheet): View
    {
        $this->authorize('merch_costing.view');

        $costSheet->load(['style', 'buyer', 'currency', 'items.item', 'preparer', 'approver']);

        return view('merchandising-trace::admin.cost-sheets.show', compact('costSheet'));
    }

    public function edit(CostSheet $costSheet): View
    {
        $this->authorize('merch_costing.edit');

        $costSheet->load('items');

        return view('merchandising-trace::admin.cost-sheets.edit', ['costSheet' => $costSheet] + $this->formOptions());
    }

    public function update(CostSheetRequest $request, CostSheet $costSheet): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $costSheet) {
            $costSheet->update([
                'currency_id' => $data['currency_id'] ?? null,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'order_qty' => $data['order_qty'] ?? null,
                'smv' => $data['smv'] ?? null,
                'cm_minute_rate' => $data['cm_minute_rate'] ?? null,
                'efficiency_percent' => $data['efficiency_percent'] ?? 100,
                'print_emb_cost' => $data['print_emb_cost'] ?? 0,
                'wash_cost' => $data['wash_cost'] ?? 0,
                'freight_cost' => $data['freight_cost'] ?? 0,
                'testing_cost' => $data['testing_cost'] ?? 0,
                'overhead_cost' => $data['overhead_cost'] ?? 0,
                'profit_percent' => $data['profit_percent'] ?? 0,
                'buyer_target_price' => $data['buyer_target_price'] ?? null,
                'final_price' => $data['final_price'] ?? null,
                'price_type' => $data['price_type'],
                'remarks' => $data['remarks'] ?? null,
            ]);

            $costSheet->items()->delete();
            foreach ($data['items'] ?? [] as $line) {
                if (empty($line['description']) && empty($line['item_id'])) {
                    continue;
                }
                $costSheet->items()->create($line);
            }

            $costSheet->recompute();
        });

        return redirect()->route('merchandising-trace.cost-sheets.show', $costSheet)->with('success', 'Cost Sheet updated successfully.');
    }

    public function destroy(CostSheet $costSheet): RedirectResponse
    {
        $this->authorize('merch_costing.delete');

        $costSheet->delete();

        return redirect()->route('merchandising-trace.cost-sheets.index')->with('success', 'Cost Sheet deleted successfully.');
    }

    /**
     * §M06: only an approved cost sheet may be linked to a sales contract.
     */
    public function approve(CostSheet $costSheet): RedirectResponse
    {
        $this->authorize('merch_costing.edit');

        $costSheet->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', "Cost Sheet {$costSheet->cost_sheet_no} approved.");
    }

    private function formOptions(): array
    {
        return [
            'stylesOptions' => Style::query()->active()->orderBy('name')->get(),
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'currenciesOptions' => Currency::query()->active()->orderBy('code')->get(),
            'itemsOptions' => Item::query()->active()->orderBy('name')->get(),
            'uomsOptions' => Uom::query()->active()->orderBy('name')->get(),
        ];
    }
}
