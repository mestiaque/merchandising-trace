<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Models\Bridge\TrcProduct;
use ME\MerchandisingTrace\Models\Bridge\TrcSizeGroup;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Services\PreFlightChecklistService;
use ME\MerchandisingTrace\Services\ProductionHandoverService;

class ProductionHandoverController extends Controller
{
    public function index(Request $request, PreFlightChecklistService $checklist): View
    {
        $this->authorize('merch_production_handover.list');

        $pos = SalesContractPo::query()
            ->with(['style', 'salesContract', 'tnaPlan'])
            ->whereNull('production_plan_line_id')
            ->whereHas('salesContract', fn ($q) => $q->where('status', 'confirmed'))
            ->latest('id')
            ->paginate(20);

        $rows = $pos->getCollection()->map(fn (SalesContractPo $po) => [
            'po' => $po,
            'checks' => $checklist->evaluate($po),
        ]);

        $stats = [
            'pending' => $pos->total(),
            'ready' => $rows->filter(fn ($r) => $r['checks']['all_passed'])->count(),
            'blocked' => $rows->filter(fn ($r) => ! $r['checks']['all_passed'])->count(),
            'handed_over' => SalesContractPo::whereNotNull('production_plan_line_id')->count(),
        ];

        return view('merchandising-trace::admin.production-handovers.index', [
            'rows' => $rows,
            'paginator' => $pos,
            'stats' => $stats,
        ]);
    }

    public function show(SalesContractPo $sales_contract_po, PreFlightChecklistService $checklist): View
    {
        $this->authorize('merch_production_handover.view');

        $sales_contract_po->load(['style', 'salesContract', 'tnaPlan', 'sizes']);

        return view('merchandising-trace::admin.production-handovers.show', [
            'po' => $sales_contract_po,
            'checks' => $checklist->evaluate($sales_contract_po),
            'trcProducts' => TrcProduct::query()->active()->orderBy('name')->get(),
            'trcSizeGroups' => TrcSizeGroup::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function mapStyle(Request $request, Style $style): RedirectResponse
    {
        $this->authorize('merch_production_handover.edit');

        $data = $request->validate([
            'trc_product_id' => ['required', 'integer'],
            'trc_size_group_id' => ['required', 'integer'],
        ]);

        $style->update($data);

        return back()->with('success', 'Style mapped to its Production Product / Size Group.');
    }

    public function push(Request $request, SalesContractPo $sales_contract_po, ProductionHandoverService $service): RedirectResponse
    {
        $this->authorize('merch_production_handover.edit');

        $data = $request->validate(['override_reason' => ['nullable', 'string', 'max:500']]);

        try {
            $handover = $service->push($sales_contract_po, auth()->id(), $data['override_reason'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('merchandising-trace.production-handovers.show', $sales_contract_po)
            ->with('success', "PO {$sales_contract_po->po_no} handed over ({$handover->pcd_status}).");
    }

    public function rollback(Request $request, SalesContractPo $sales_contract_po, ProductionHandoverService $service): RedirectResponse
    {
        $this->authorize('merch_production_handover.edit');

        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            $service->rollback($sales_contract_po, $data['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Handover rolled back.');
    }
}
