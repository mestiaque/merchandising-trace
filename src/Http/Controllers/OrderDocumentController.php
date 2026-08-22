<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ME\MerchandisingTrace\Models\OrderDocument;
use ME\MerchandisingTrace\Models\SalesContract;

class OrderDocumentController extends Controller
{
    public function index(SalesContract $salesContract): \Illuminate\View\View
    {
        $this->authorize('merch_documentation.list');

        return view('merchandising-trace::admin.order-documents.index', [
            'salesContract' => $salesContract->load('documents'),
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
