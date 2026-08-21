<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\DocumentRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Document;
use ME\MerchandisingTrace\Models\Order;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_document.list');

        $documents = Document::query()
            ->with(['buyer', 'order'])
            ->when($request->filled('document_type'), fn ($q) => $q->where('document_type', $request->document_type))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.documents.index', ['documents' => $documents] + $this->formOptions());
    }

    public function store(DocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Document::create($data);

        return back()->with('success', 'Document added successfully.');
    }

    public function update(DocumentRequest $request, Document $document): RedirectResponse
    {
        $document->update($request->validated());

        return back()->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('merch_document.delete');

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'ordersOptions' => Order::query()->orderByDesc('id')->limit(200)->get(),
        ];
    }
}
