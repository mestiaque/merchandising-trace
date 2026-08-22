<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\TnaSubPlanRequest;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\TnaSubPlan;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class TnaSubPlanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_tna.list');

        $subPlans = TnaSubPlan::query()
            ->with(['salesContractPo.style', 'vendor'])
            ->when($request->filled('process_type'), fn ($q) => $q->where('process_type', $request->process_type))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.tna-sub-plans.index', ['subPlans' => $subPlans]);
    }

    public function create(): View
    {
        $this->authorize('merch_tna.add');

        return view('merchandising-trace::admin.tna-sub-plans.create', $this->formOptions());
    }

    public function store(TnaSubPlanRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();
        $data['sub_no'] = $numbers->next(TnaSubPlan::class, 'sub_no', 'SUB');
        $data['status'] = 'open';

        $subPlan = TnaSubPlan::create($data);

        return redirect()->route('merchandising-trace.tna-sub-plans.show', $subPlan)->with('success', "Sub-T&A {$subPlan->sub_no} created successfully.");
    }

    public function show(TnaSubPlan $tnaSubPlan): View
    {
        $this->authorize('merch_tna.view');

        $tnaSubPlan->load(['salesContractPo.style', 'vendor', 'logs']);

        return view('merchandising-trace::admin.tna-sub-plans.show', ['subPlan' => $tnaSubPlan]);
    }

    public function addLog(Request $request, TnaSubPlan $tnaSubPlan): RedirectResponse
    {
        $this->authorize('merch_tna.edit');

        $data = $request->validate([
            'log_date' => ['required', 'date'],
            'sending_qty' => ['required', 'integer', 'min:0'],
            'receiving_qty' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        // §4.8.1 hard rules: sending can't exceed PO qty; receiving can't
        // exceed sending cum.
        $sentSoFar = $tnaSubPlan->totalSent();
        $receivedSoFar = $tnaSubPlan->totalReceived();

        if ($sentSoFar + $data['sending_qty'] > $tnaSubPlan->po_qty) {
            return back()->withInput()->with('error', 'Sending qty would exceed the PO qty.');
        }
        if ($receivedSoFar + $data['receiving_qty'] > $sentSoFar + $data['sending_qty']) {
            return back()->withInput()->with('error', 'Receiving qty cannot exceed sending cum.');
        }

        $tnaSubPlan->logs()->updateOrCreate(['log_date' => $data['log_date']], [
            'sending_qty' => $data['sending_qty'],
            'receiving_qty' => $data['receiving_qty'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        if ($tnaSubPlan->status === 'open') {
            $tnaSubPlan->update(['status' => 'running']);
        }
        if ($tnaSubPlan->balanceQty() <= 0) {
            $tnaSubPlan->update(['status' => 'completed']);
        }

        return back()->with('success', 'Daily log saved.');
    }

    private function formOptions(): array
    {
        return [
            'posOptions' => SalesContractPo::query()->with(['style', 'salesContract.buyer'])->latest('id')->limit(200)->get(),
            'vendorsOptions' => Supplier::query()->active()->orderBy('name')->get(),
        ];
    }
}
