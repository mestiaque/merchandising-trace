<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Exports\GenericArrayExport;
use ME\MerchandisingTrace\Http\Requests\TnaPlanCreateRequest;
use ME\MerchandisingTrace\Imports\TnaGridImport;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Factory;
use ME\MerchandisingTrace\Models\ProductType;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\ShipMode;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\TnaPlan;
use ME\MerchandisingTrace\Models\TnaTask;
use ME\MerchandisingTrace\Models\TnaTemplate;
use ME\MerchandisingTrace\Models\WashType;
use App\Models\User;
use ME\MerchandisingTrace\Services\DocumentChecklistService;
use ME\MerchandisingTrace\Services\DocumentNumberService;
use ME\MerchandisingTrace\Services\PcdGateService;
use ME\MerchandisingTrace\Services\TnaGridExportService;
use ME\MerchandisingTrace\Services\TnaGridImportService;
use ME\MerchandisingTrace\Services\TnaPlanGenerationService;

class TnaPlanController extends Controller
{
    /**
     * §8.3: this list view gives the same filters (buyer, PCD result,
     * at-risk) plus row-level completion/status/countdown, with each row
     * opening the full grouped task view in show(). See grid() below for
     * the actual frozen-column spreadsheet screen.
     */
    public function index(Request $request): View
    {
        $this->authorize('merch_tna.list');

        $plans = TnaPlan::query()
            ->with(['salesContractPo.salesContract.buyer', 'salesContractPo.style'])
            ->when($request->filled('buyer_id'), fn ($q) => $q->whereHas('salesContractPo.salesContract', fn ($qq) => $qq->where('buyer_id', $request->buyer_id)))
            ->when($request->filled('pcd_result'), fn ($q) => $q->where('pcd_result', $request->pcd_result))
            ->when($request->filled('overall_status'), fn ($q) => $q->where('overall_status', $request->overall_status))
            ->when($request->boolean('my_orders'), fn ($q) => $q->where('merchandiser_id', auth()->id()))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => TnaPlan::count(),
            'pcd_pass' => TnaPlan::where('pcd_result', 'pass')->count(),
            'pcd_fail' => TnaPlan::where('pcd_result', 'fail')->count(),
            'pcd_pending' => TnaPlan::where('pcd_result', 'pending')->count(),
            'at_risk_or_delayed' => TnaPlan::whereIn('overall_status', ['at_risk', 'delayed'])->count(),
        ];

        return view('merchandising-trace::admin.tna-plans.index', [
            'plans' => $plans,
            'stats' => $stats,
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
        ]);
    }

    /**
     * "Add T&A Plan" shortcut — gathers Buyer + Style + PO on one screen
     * instead of requiring the Sales Contract → PO → Confirm flow first.
     */
    public function create(): View
    {
        $this->authorize('merch_tna.add');

        return view('merchandising-trace::admin.tna-plans.create', $this->createFormOptions());
    }

    public function store(TnaPlanCreateRequest $request, DocumentNumberService $numbers, TnaPlanGenerationService $tnaGenerator, DocumentChecklistService $documents): RedirectResponse
    {
        $data = $request->validated();

        $plan = DB::transaction(function () use ($data, $numbers, $tnaGenerator, $documents) {
            $contract = SalesContract::create([
                'contract_no' => $numbers->next(SalesContract::class, 'contract_no', 'SC'),
                'buyer_id' => $data['buyer_id'],
                'season_id' => $data['season_id'] ?? null,
                'merchandiser_id' => $data['merchandiser_id'] ?? null,
                'factory_id' => $data['factory_id'] ?? null,
                'contract_date' => $data['contract_date'],
                'status' => 'confirmed',
                'created_by' => auth()->id(),
            ]);

            $po = SalesContractPo::create([
                'sales_contract_id' => $contract->id,
                'style_id' => $data['style_id'],
                'product_type_id' => $data['product_type_id'] ?? null,
                'color_id' => $data['color_id'],
                'wash_type_id' => $data['wash_type_id'] ?? null,
                'po_no' => $data['po_no'],
                'po_due_date' => $data['po_due_date'] ?? null,
                'po_qty' => $data['po_qty'],
                'unit_price' => $data['unit_price'] ?? null,
                'total_value' => ($data['unit_price'] ?? 0) * $data['po_qty'],
                'price_type' => $data['price_type'] ?? null,
                'pcd_date' => $data['pcd_date'] ?? null,
                'shipment_date' => $data['shipment_date'] ?? null,
                'ship_mode_id' => $data['ship_mode_id'] ?? null,
                'print_emb' => $data['print_emb'] ?? 'na',
                'emb_applique_ih' => $data['emb_applique_ih'] ?? 'na',
                'studs_stones_ih' => $data['studs_stones_ih'] ?? 'na',
                'heat_seal_ih' => $data['heat_seal_ih'] ?? 'na',
                'status' => 'pending',
            ]);

            foreach ($data['sizes'] as $line) {
                $po->sizes()->create(['size_id' => $line['size_id'], 'qty' => $line['qty']]);
            }

            $contract->refreshTotals();

            $plan = $tnaGenerator->generateFor($po);
            $po->update(['status' => 'tna_created']);
            $documents->generateFor($contract);

            return $plan;
        });

        return redirect()->route('merchandising-trace.tna-plans.show', $plan)->with('success', "T&A plan {$plan->tna_no} created for PO {$data['po_no']}.");
    }

    private function createFormOptions(): array
    {
        return [
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'seasonsOptions' => Season::query()->active()->orderBy('name')->get(),
            'merchandisersOptions' => User::query()->orderBy('name')->get(),
            'factoriesOptions' => Factory::query()->active()->orderBy('name')->get(),
            'stylesOptions' => Style::query()->active()->orderBy('style_no')->get(),
            'colorsOptions' => Color::query()->active()->orderBy('name')->get(),
            'productTypesOptions' => ProductType::query()->active()->orderBy('name')->get(),
            'washTypesOptions' => WashType::query()->active()->orderBy('name')->get(),
            'shipModesOptions' => ShipMode::query()->active()->orderBy('name')->get(),
            'sizesOptions' => Size::query()->active()->orderBy('sort_order')->get(),
        ];
    }

    /**
     * §8.3 — "The T&A Grid (the main screen — must look like the Excel)".
     * Frozen leading columns (Merchant/Buyer/Style/PO/Color/PO Qty) +
     * grouped two-row header (group band, task caption) + colour-coded,
     * inline-editable task cells (via tasks.update, AJAX). Column set is
     * the DEFAULT template's tasks — every row uses the same columns
     * regardless of which template actually generated that row's plan, so
     * the grid stays visually consistent; a row simply shows '-' for a
     * task_code its own plan doesn't have.
     */
    public function grid(Request $request): View
    {
        $this->authorize('merch_tna.list');

        $plans = TnaPlan::query()
            ->with(['salesContractPo.salesContract.buyer', 'salesContractPo.style', 'salesContractPo.color', 'merchandiser', 'tasks'])
            ->when($request->filled('buyer_id'), fn ($q) => $q->whereHas('salesContractPo.salesContract', fn ($qq) => $qq->where('buyer_id', $request->buyer_id)))
            ->when($request->filled('pcd_result'), fn ($q) => $q->where('pcd_result', $request->pcd_result))
            ->when($request->filled('overall_status'), fn ($q) => $q->where('overall_status', $request->overall_status))
            ->when($request->boolean('my_orders'), fn ($q) => $q->where('merchandiser_id', auth()->id()))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $template = TnaTemplate::query()->where('is_default', true)->active()->first()
            ?? TnaTemplate::query()->active()->first();
        $columns = $template ? $template->tasks : collect();
        $columnGroups = $columns->groupBy('group_name');

        return view('merchandising-trace::admin.tna-plans.grid', [
            'plans' => $plans,
            'columnGroups' => $columnGroups,
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function show(TnaPlan $tnaPlan): View
    {
        $this->authorize('merch_tna.view');

        $tnaPlan->load([
            'salesContractPo.salesContract.buyer', 'salesContractPo.style', 'salesContractPo.color',
            'tasks.responsibleDept', 'merchandiser', 'factory', 'responsibleDept', 'responsiblePerson',
        ]);

        $tasksByGroup = $tnaPlan->tasks->groupBy('group_name');

        return view('merchandising-trace::admin.tna-plans.show', compact('tnaPlan', 'tasksByGroup'));
    }

    /**
     * §8.3 inline cell edit — one task's date/status/text/number per request.
     * Auto-filled cells (is_auto) are rejected here too, mirroring the
     * read-only rule client-side. Route-model binding resolves {tna_plan}
     * and {task} independently by their own primary keys, so the mismatch
     * guard below is required — without it, a task belonging to a
     * different plan could be updated while recomputeCompletion() runs
     * against the WRONG (URL) plan, corrupting its completion % (the same
     * bug class fixed in SalesContractPoController).
     */
    public function updateTask(Request $request, TnaPlan $tnaPlan, TnaTask $task): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('merch_tna.edit');
        abort_unless($task->tna_plan_id === $tnaPlan->id, 404);

        if ($task->is_auto) {
            $message = 'This cell is auto-filled from another module and cannot be edited manually.';

            return $request->ajax() || $request->wantsJson()
                ? response()->json(['ok' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $data = $request->validate([
            'plan_date' => ['nullable', 'date'],
            'revised_date' => ['nullable', 'date'],
            'actual_date' => ['nullable', 'date'],
            'value_text' => ['nullable', 'string', 'max:255'],
            'value_number' => ['nullable', 'numeric'],
            'status' => ['required', 'string', 'in:' . implode(',', TnaTask::STATUSES)],
        ]);

        foreach (['plan_date', 'revised_date', 'actual_date', 'value_text', 'value_number', 'status'] as $field) {
            if (! array_key_exists($field, $data) || (string) $data[$field] === (string) $task->{$field}) {
                continue;
            }

            $task->logs()->create([
                'field' => $field,
                'old_value' => $task->{$field},
                'new_value' => $data[$field],
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        }

        $task->update($data);
        $tnaPlan->recomputeCompletion();

        if ($request->ajax() || $request->wantsJson()) {
            $task->refresh();

            return response()->json([
                'ok' => true,
                'message' => "Task '{$task->task_name}' updated.",
                'task' => [
                    'id' => $task->id,
                    'display' => $this->cellDisplay($task),
                    'color' => $task->boardColor(),
                    'status' => $task->status,
                ],
            ]);
        }

        return back()->with('success', "Task '{$task->task_name}' updated.");
    }

    private function cellDisplay(TnaTask $task): string
    {
        return match ($task->value_type) {
            'date' => $task->actual_date?->format('d-M-Y') ?? '-',
            'number' => $task->value_number !== null ? (string) $task->value_number : '-',
            default => $task->value_text ?? '-',
        };
    }

    public function evaluatePcd(TnaPlan $tnaPlan, PcdGateService $gate): RedirectResponse
    {
        $this->authorize('merch_tna.view');

        $gate->evaluate($tnaPlan);

        return back()->with('success', 'PCD evaluated: ' . strtoupper($tnaPlan->fresh()->pcd_result) . '.');
    }

    public function overridePcd(Request $request, TnaPlan $tnaPlan, PcdGateService $gate): RedirectResponse
    {
        $this->authorize('merch_tna.override_pcd');

        $request->validate(['reason' => ['required', 'string']]);

        $gate->override($tnaPlan, $request->reason, auth()->id());

        return back()->with('success', 'PCD result overridden to PASS.');
    }

    /**
     * §8.3 / test #15: export in the same PO-No.-keyed layout the importer
     * reads back.
     */
    public function exportExcel(Request $request, TnaGridExportService $export)
    {
        $this->authorize('merch_tna.view');

        $data = $export->export($request->only(['buyer_id']));

        return \Maatwebsite\Excel\Facades\Excel::download(
            new GenericArrayExport($data['headers'], $data['rows']),
            'tna-grid.xlsx'
        );
    }

    public function importExcel(Request $request, TnaGridImportService $importer): RedirectResponse
    {
        $this->authorize('merch_tna.edit');

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new TnaGridImport(), $request->file('file'));
        $result = $importer->import($sheets[0] ?? [], auth()->id());

        return back()->with('success', "T&A grid imported: {$result['updated']} cell(s) updated, {$result['skipped_auto']} auto-filled cell(s) skipped, {$result['po_not_found']} PO(s) not found.");
    }
}
