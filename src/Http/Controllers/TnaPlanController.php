<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Exports\GenericArrayExport;
use ME\MerchandisingTrace\Imports\TnaGridImport;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\TnaPlan;
use ME\MerchandisingTrace\Models\TnaTask;
use ME\MerchandisingTrace\Services\PcdGateService;
use ME\MerchandisingTrace\Services\TnaGridExportService;
use ME\MerchandisingTrace\Services\TnaGridImportService;

class TnaPlanController extends Controller
{
    /**
     * §8.3: the T&A grid — frozen-column spreadsheet is a heavier client-side
     * build; this list gives the same filters (buyer, PCD result, at-risk)
     * plus row-level completion/status/countdown, with each row opening the
     * full grouped task view in show().
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

        return view('merchandising-trace::admin.tna-plans.index', [
            'plans' => $plans,
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
     * read-only rule client-side.
     */
    public function updateTask(Request $request, TnaPlan $tnaPlan, TnaTask $task): RedirectResponse
    {
        $this->authorize('merch_tna.edit');

        if ($task->is_auto) {
            return back()->with('error', 'This cell is auto-filled from another module and cannot be edited manually.');
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

        return back()->with('success', "Task '{$task->task_name}' updated.");
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
