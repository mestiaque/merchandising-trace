<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Exports\GenericArrayExport;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Services\ReportService;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('merch_reports.list');

        return view('merchandising-trace::admin.reports.index', [
            'reports' => ReportService::REPORTS,
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, string $key, ReportService $reports): View
    {
        $this->authorize('merch_reports.view');

        abort_unless(array_key_exists($key, ReportService::REPORTS), 404);

        $filters = $request->only(['buyer_id', 'merchandiser_id', 'style_id', 'po_no', 'date_from', 'date_to']);
        $data = $reports->run($key, array_filter($filters));

        return view('merchandising-trace::admin.reports.show', [
            'key' => $key,
            'title' => ReportService::REPORTS[$key],
            'headers' => $data['headers'],
            'rows' => $data['rows'],
            'filters' => $filters,
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function exportExcel(Request $request, string $key, ReportService $reports)
    {
        $this->authorize('merch_reports.view');

        abort_unless(array_key_exists($key, ReportService::REPORTS), 404);

        $filters = array_filter($request->only(['buyer_id', 'merchandiser_id', 'style_id', 'po_no', 'date_from', 'date_to']));
        $data = $reports->run($key, $filters);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new GenericArrayExport($data['headers'], $data['rows']),
            "{$key}.xlsx"
        );
    }

    public function exportPdf(Request $request, string $key, ReportService $reports)
    {
        $this->authorize('merch_reports.view');

        abort_unless(array_key_exists($key, ReportService::REPORTS), 404);

        $filters = array_filter($request->only(['buyer_id', 'merchandiser_id', 'style_id', 'po_no', 'date_from', 'date_to']));
        $data = $reports->run($key, $filters);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('merchandising-trace::admin.reports.pdf', [
            'title' => ReportService::REPORTS[$key],
            'headers' => $data['headers'],
            'rows' => $data['rows'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download("{$key}.pdf");
    }
}
