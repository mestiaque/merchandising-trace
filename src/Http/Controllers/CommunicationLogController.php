<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Models\CommunicationLog;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\Style;

class CommunicationLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_communication.list');

        $logs = CommunicationLog::query()
            ->with(['style', 'salesContractPo'])
            ->search($request->input('search'))
            ->when($request->filled('style_id'), fn ($q) => $q->where('style_id', $request->style_id))
            ->latest('log_date')
            ->paginate(30)
            ->withQueryString();

        return view('merchandising-trace::admin.communication-logs.index', [
            'logs' => $logs,
            'stylesOptions' => Style::query()->active()->orderBy('name')->limit(200)->get(),
            'posOptions' => SalesContractPo::query()->latest('id')->limit(200)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('merch_communication.add');

        $data = $request->validate([
            'style_id' => ['nullable', 'integer', 'exists:mer_styles,id'],
            'sales_contract_po_id' => ['nullable', 'integer', 'exists:mer_sales_contract_pos,id'],
            'log_date' => ['required', 'date'],
            'direction' => ['required', 'string', 'in:' . implode(',', CommunicationLog::DIRECTIONS)],
            'channel' => ['required', 'string', 'in:' . implode(',', CommunicationLog::CHANNELS)],
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $data['created_by'] = auth()->id();
        CommunicationLog::create($data);

        return back()->with('success', 'Communication logged.');
    }
}
