<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Read-only visibility into the production floor (Line Booking / Capacity /
 * Follow-up / Status) for merchandisers — Merchandising owns no tables here.
 * production-sfl depends on this package, not the other way around, so this
 * queries its tables directly by name (DB::table, not an Eloquent relation)
 * rather than importing its models.
 */
class ProductionCoordinationController extends Controller
{
    public function index(): View
    {
        $this->authorize('merch_production_coordination.view');

        $lineBookings = DB::table('pro_line_jobs as j')
            ->join('mer_orders as o', 'o.id', '=', 'j.order_id')
            ->join('pro_production_lines as pl', 'pl.id', '=', 'j.production_line_id')
            ->leftJoin('mer_buyers as b', 'b.id', '=', 'o.buyer_id')
            ->select([
                'j.id', 'o.po_number', 'b.name as buyer_name', 'pl.name as line_name',
                'j.alloc_qty', 'j.target_qty', 'j.status', 'j.start_date', 'j.end_date',
            ])
            ->orderByDesc('j.id')
            ->limit(200)
            ->get();

        $producedByJob = DB::table('pro_line_hourly_entries as h')
            ->join('pro_line_daily_entries as e', 'e.id', '=', 'h.line_daily_entry_id')
            ->select('e.line_job_id', DB::raw('SUM(h.produced_qty) as produced'))
            ->groupBy('e.line_job_id')
            ->pluck('produced', 'line_job_id');

        $statusCounts = DB::table('pro_line_jobs')->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        return view('merchandising-trace::admin.production-coordination.index', [
            'lineBookings'   => $lineBookings,
            'producedByJob'  => $producedByJob,
            'statusCounts'   => $statusCounts,
        ]);
    }
}
