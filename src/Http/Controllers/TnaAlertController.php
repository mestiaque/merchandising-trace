<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Models\TnaAlert;

class TnaAlertController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_tna.view');

        $alerts = TnaAlert::query()
            ->with(['task.plan.salesContractPo.style'])
            ->where('notified_to', auth()->id())
            ->when($request->boolean('unread_only'), fn ($q) => $q->where('is_read', false))
            ->latest('alert_date')
            ->paginate(30)
            ->withQueryString();

        return view('merchandising-trace::admin.tna-alerts.index', ['alerts' => $alerts]);
    }

    public function markRead(TnaAlert $tnaAlert): RedirectResponse
    {
        $tnaAlert->update(['is_read' => true]);

        return back()->with('success', 'Alert marked read.');
    }
}
