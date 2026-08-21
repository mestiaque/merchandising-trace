<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\PaymentTermRequest;
use ME\MerchandisingTrace\Models\PaymentTerm;

class PaymentTermController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_payment_term.list');

        $payment_terms = PaymentTerm::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.payment-terms.index', ['payment_terms' => $payment_terms]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_payment_term.list');

        $payment_terms = PaymentTerm::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Payment Terms',
            'columns' => ['name' => 'Name', 'code' => 'Code', 'days' => 'Days'],
            'rows'    => $payment_terms,
        ]);
    }

    public function store(PaymentTermRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        PaymentTerm::create($data);

        return back()->with('success', 'Payment Term created successfully.');
    }

    public function update(PaymentTermRequest $request, PaymentTerm $payment_term): RedirectResponse
    {
        $payment_term->update($request->validated());

        return back()->with('success', 'Payment Term updated successfully.');
    }

    public function destroy(PaymentTerm $payment_term): RedirectResponse
    {
        $this->authorize('merch_payment_term.delete');

        $payment_term->delete();

        return back()->with('success', 'Payment Term deleted successfully.');
    }
}
