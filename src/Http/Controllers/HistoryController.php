<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Services\HistoryService;

class HistoryController extends Controller
{
    public function __construct(private readonly HistoryService $history)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('merch_history.list');

        $q = trim((string) $request->input('q'));
        $results = $q !== '' ? $this->history->search($q) : ['pos' => collect(), 'styles' => collect(), 'buyers' => collect()];

        return view('merchandising-trace::admin.history.index', ['q' => $q] + $results);
    }

    public function showPo(SalesContractPo $salesContractPo): View
    {
        $this->authorize('merch_history.view');

        $salesContractPo->load(['style.buyer', 'salesContract.buyer']);
        $events = $this->history->timelineForPo($salesContractPo);

        return view('merchandising-trace::admin.history.show', [
            'subjectType' => 'po',
            'subject' => $salesContractPo,
            'title' => "PO {$salesContractPo->po_no}",
            'subtitle' => ($salesContractPo->style->style_no ?? '-') . ' — ' . ($salesContractPo->salesContract->buyer->name ?? '-'),
            'events' => $events,
        ]);
    }

    public function showStyle(Style $style): View
    {
        $this->authorize('merch_history.view');

        $style->load('buyer');
        $events = $this->history->timelineForStyle($style);

        return view('merchandising-trace::admin.history.show', [
            'subjectType' => 'style',
            'subject' => $style,
            'title' => "Style {$style->style_no}",
            'subtitle' => ($style->name ?? '-') . ' — ' . ($style->buyer->name ?? '-'),
            'events' => $events,
        ]);
    }
}
