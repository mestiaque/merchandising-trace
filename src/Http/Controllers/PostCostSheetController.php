<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\CostSheetItem;
use ME\MerchandisingTrace\Models\PostCostSheet;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\Uom;
use ME\MerchandisingTrace\Services\PostCostService;

/**
 * Post Cost Sheet — budget (approved pre-cost) vs actual. See PostCostService
 * for where each actual figure comes from.
 */
class PostCostSheetController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_post_costing.list');

        $sheets = PostCostSheet::query()
            ->with(['style', 'buyer', 'costSheet', 'salesContract'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('post_cost_no', 'like', '%' . $request->search . '%')
                ->orWhere('style_ref', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.post-cost-sheets.index', ['sheets' => $sheets]);
    }

    public function create(Request $request): View
    {
        $this->authorize('merch_post_costing.add');

        return view('merchandising-trace::admin.post-cost-sheets.create', [
            'preCostsOptions' => CostSheet::query()->with(['buyer', 'style'])->where('status', 'approved')->latest('id')->get(),
            'contractsOptions' => SalesContract::query()->with('buyer')->latest('id')->limit(300)->get(),
            'selectedPreCost' => $request->input('cost_sheet_id'),
        ]);
    }

    public function store(Request $request, PostCostService $service): RedirectResponse
    {
        $this->authorize('merch_post_costing.add');

        $data = $request->validate([
            'cost_sheet_id' => ['required', 'integer', Rule::exists('mer_cost_sheets', 'id')->where('status', 'approved')->whereNull('deleted_at')],
            'sales_contract_id' => ['nullable', 'integer', 'exists:mer_sales_contracts,id'],
        ], ['cost_sheet_id.exists' => 'Only an approved pre-cost sheet can be post-costed.']);

        $post = $service->createFromPreCost(
            CostSheet::findOrFail($data['cost_sheet_id']),
            isset($data['sales_contract_id']) ? SalesContract::find($data['sales_contract_id']) : null,
            auth()->id()
        );

        return redirect()->route('merchandising-trace.post-cost-sheets.edit', $post)
            ->with('success', "Post Cost Sheet {$post->post_cost_no} created — review the actual figures and save.");
    }

    public function show(PostCostSheet $postCostSheet): View
    {
        $this->authorize('merch_post_costing.view');

        $postCostSheet->load(['items.uom', 'items.item', 'buyer', 'style', 'currency', 'costSheet', 'salesContract', 'preparer', 'approver']);

        return view('merchandising-trace::admin.post-cost-sheets.show', ['sheet' => $postCostSheet]);
    }

    public function edit(PostCostSheet $postCostSheet): View
    {
        $this->authorize('merch_post_costing.edit');
        abort_if($postCostSheet->status === 'approved', 403, 'An approved post cost sheet is locked.');

        $postCostSheet->load(['items.uom', 'items.item', 'buyer', 'style', 'costSheet', 'salesContract']);

        return view('merchandising-trace::admin.post-cost-sheets.edit', [
            'sheet' => $postCostSheet,
            'uomsOptions' => Uom::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PostCostSheet $postCostSheet): RedirectResponse
    {
        $this->authorize('merch_post_costing.edit');
        abort_if($postCostSheet->status === 'approved', 403, 'An approved post cost sheet is locked.');

        $data = $request->validate([
            'costing_date' => ['nullable', 'date'],
            'shipped_qty' => ['nullable', 'integer', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'actual_smv' => ['nullable', 'numeric', 'min:0'],
            'actual_cm_per_dozen' => ['nullable', 'numeric', 'min:0'],
            'actual_commercial_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'actual_other_cost' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
            'lines' => ['nullable', 'array'],
            'lines.*.id' => ['nullable', 'integer'],
            'lines.*.group' => ['required', Rule::in(array_keys(CostSheetItem::GROUPS))],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.supplier_name' => ['nullable', 'string', 'max:150'],
            'lines.*.uom_id' => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'lines.*.actual_consumption' => ['nullable', 'numeric', 'min:0'],
            'lines.*.actual_rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data, $postCostSheet) {
            $postCostSheet->update([
                'costing_date' => $data['costing_date'] ?? $postCostSheet->costing_date,
                'shipped_qty' => (int) ($data['shipped_qty'] ?? 0),
                'selling_price' => $data['selling_price'] ?? null,
                'actual_smv' => $data['actual_smv'] ?? null,
                'actual_cm_cost' => (float) ($data['actual_cm_per_dozen'] ?? 0) / 12,
                'actual_commercial_percent' => $data['actual_commercial_percent'] ?? 0,
                'actual_other_cost' => $data['actual_other_cost'] ?? 0,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $keep = [];
            foreach ($data['lines'] ?? [] as $row) {
                $line = ! empty($row['id']) ? $postCostSheet->items()->find($row['id']) : null;
                if (! $line) {
                    // Budget-less line added on the post cost (an expense the pre-cost missed).
                    if (empty($row['description']) && empty($row['actual_rate'])) {
                        continue;
                    }
                    $line = $postCostSheet->items()->make(['group' => $row['group'], 'source' => 'manual']);
                }
                $line->fill([
                    'description' => $row['description'] ?? $line->description,
                    'supplier_name' => $row['supplier_name'] ?? null,
                    'uom_id' => $row['uom_id'] ?? $line->uom_id,
                    'actual_consumption' => $row['actual_consumption'] ?? 0,
                    'actual_rate' => $row['actual_rate'] ?? 0,
                    'remarks' => $row['remarks'] ?? null,
                ]);
                $line->save();
                $keep[] = $line->id;
            }
            // Lines removed on the form: only budget-less ones may go; budget lines stay (set actual to 0 instead).
            $postCostSheet->items()->whereNotIn('id', $keep)->where('budget_amount', 0)->delete();

            $postCostSheet->recompute();
        });

        return redirect()->route('merchandising-trace.post-cost-sheets.show', $postCostSheet)->with('success', 'Post Cost Sheet saved.');
    }

    public function refresh(PostCostSheet $postCostSheet, PostCostService $service): RedirectResponse
    {
        $this->authorize('merch_post_costing.edit');
        abort_if($postCostSheet->status === 'approved', 403, 'An approved post cost sheet is locked.');

        $n = $service->refreshActuals($postCostSheet);

        return back()->with('success', $n
            ? "Actuals refreshed from material bookings / receipts ({$n} line(s)) and shipped qty from production."
            : 'No booked materials found for this style — actuals left as entered.');
    }

    public function approve(PostCostSheet $postCostSheet): RedirectResponse
    {
        $this->authorize('merch_post_costing.edit');

        $postCostSheet->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', "Post Cost Sheet {$postCostSheet->post_cost_no} approved and locked.");
    }

    public function print(PostCostSheet $postCostSheet): View
    {
        $this->authorize('merch_post_costing.view');

        $postCostSheet->load(['items.uom', 'items.item', 'buyer', 'style', 'currency', 'costSheet', 'salesContract']);

        return view('merchandising-trace::admin.post-cost-sheets.print', ['sheet' => $postCostSheet]);
    }

    public function destroy(PostCostSheet $postCostSheet): RedirectResponse
    {
        $this->authorize('merch_post_costing.delete');

        $postCostSheet->delete();

        return redirect()->route('merchandising-trace.post-cost-sheets.index')->with('success', 'Post Cost Sheet deleted.');
    }
}
