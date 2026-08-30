<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\OrderDocument;
use ME\MerchandisingTrace\Models\RiskAssessment;
use ME\MerchandisingTrace\Models\SalesContract;

class OrderDocumentController extends Controller
{
    /**
     * §M13 — the order-wise document hub: the buyer checklist below, plus
     * every buyer-format document already generated elsewhere in the
     * pipeline (PO print, Cost Sheet PDF, Risk Assessment, tech pack file)
     * for every style used on this order, gathered in one place.
     */
    public function index(SalesContract $salesContract): \Illuminate\View\View
    {
        $this->authorize('merch_documentation.list');

        $salesContract->load(['documents', 'pos.style']);
        $styleIds = $salesContract->pos->pluck('style_id')->filter()->unique();

        return view('merchandising-trace::admin.order-documents.index', [
            'salesContract' => $salesContract,
            'costSheets' => CostSheet::query()->whereIn('style_id', $styleIds)->latest('version')->get(),
            'riskAssessments' => RiskAssessment::query()->whereIn('style_id', $styleIds)->latest('id')->get(),
        ]);
    }

    public function upload(Request $request, SalesContract $salesContract, OrderDocument $document): RedirectResponse
    {
        $this->authorize('merch_documentation.edit');

        $request->validate(['file' => ['required', 'file', 'max:10240']]);

        $path = $request->file('file')->store('merchandising-trace/order-documents', 'public');

        $document->update([
            'file_path' => $path,
            'status' => 'uploaded',
            'uploaded_at' => now(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', "{$document->name} uploaded.");
    }

    public function approve(SalesContract $salesContract, OrderDocument $document): RedirectResponse
    {
        $this->authorize('merch_documentation.edit');

        $document->update(['status' => 'approved']);

        return back()->with('success', "{$document->name} approved.");
    }
}
