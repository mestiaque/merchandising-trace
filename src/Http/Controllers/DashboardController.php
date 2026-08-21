<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Contracts\View\View;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Costing;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\TnaMilestone;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('merch_dashboard.view');

        return view('merchandising-trace::admin.dashboard', self::stats());
    }

    /**
     * Static + try/catch-wrapped so it can be safely embedded elsewhere (per
     * the host app's established pattern for other module dashboard widgets,
     * e.g. ME\ProductionSfl\Http\Controllers\DashboardController::stats()). A
     * failure here must never break a shared aggregate dashboard page.
     */
    public static function stats(): array
    {
        try {
            $totalBuyers = Buyer::query()->active()->count();
            $runningOrders = Order::status('in_production')->count();
            $pendingSamples = Sample::query()->pending()->count();
            $pendingMaterialBookings = MaterialBooking::where('status', 'booked')->count();
            $delayedMilestones = TnaMilestone::delayed()->count();
            $totalOrderValue = (float) Order::query()->selectRaw('SUM(order_qty * price) as total')->value('total');

            $approvedCostings = Costing::where('status', 'approved')->get();
            $avgMargin = $approvedCostings->isNotEmpty()
                ? round($approvedCostings->avg(fn (Costing $c) => $c->profitMarginPercent()), 1)
                : 0.0;

            $orderTrend = collect(range(5, 0))->map(function ($monthsAgo) {
                $month = now()->subMonths($monthsAgo);

                return [
                    'label' => $month->format('M Y'),
                    'count' => Order::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count(),
                ];
            });

            $statusBreakdown = Order::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $topBuyers = Order::query()
                ->selectRaw('buyer_id, SUM(order_qty) as total_qty, COUNT(*) as order_count')
                ->groupBy('buyer_id')
                ->orderByDesc('total_qty')
                ->with('buyer')
                ->take(5)
                ->get();

            return [
                'totalBuyers'             => $totalBuyers,
                'runningOrders'           => $runningOrders,
                'pendingSamples'          => $pendingSamples,
                'pendingMaterialBookings' => $pendingMaterialBookings,
                'delayedMilestones'       => $delayedMilestones,
                'totalOrderValue'         => $totalOrderValue,
                'avgMargin'               => $avgMargin,
                'orderTrend'              => $orderTrend,
                'statusBreakdown'         => $statusBreakdown,
                'topBuyers'               => $topBuyers,
                'totalOrders'             => Order::count(),
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
