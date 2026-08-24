<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\View\View;
use ME\MerchandisingTrace\Services\DashboardService;

class DashboardController extends Controller
{
    /**
     * §M15 — one dashboard, matching the house convention used by every
     * sibling package (one route, one action, one view). Everyone with
     * dashboard access sees their own "My" section; a management section
     * is appended below it only for users holding merch_dashboard.view_all
     * (@can inside the view), rather than living on a second page.
     */
    public function index(DashboardService $dashboard): View
    {
        $this->authorize('merch_dashboard.view');

        $data = ['mine' => $dashboard->merchandiser(auth()->id())];

        if (auth()->user()?->can('merch_dashboard.view_all')) {
            $data['management'] = $dashboard->management();
        }

        return view('merchandising-trace::admin.dashboard', $data);
    }

    /**
     * Static + try/catch-wrapped so it can be safely embedded on the host
     * app's own aggregate dashboard, mirroring the pattern used by every
     * other module (e.g. production-trace's TrcDashboardController::stats()).
     * A failure here must never break a shared dashboard page.
     */
    public static function stats(): array
    {
        try {
            return app(DashboardService::class)->overview();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
