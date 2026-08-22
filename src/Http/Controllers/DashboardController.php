<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\View\View;
use ME\MerchandisingTrace\Services\DashboardService;

class DashboardController extends Controller
{
    public function merchandiser(DashboardService $dashboard): View
    {
        $this->authorize('merch_dashboard.view');

        return view('merchandising-trace::admin.dashboards.merchandiser', [
            'data' => $dashboard->merchandiser(auth()->id()),
        ]);
    }

    public function management(DashboardService $dashboard): View
    {
        $this->authorize('merch_dashboard.view_all');

        return view('merchandising-trace::admin.dashboards.management', [
            'data' => $dashboard->management(),
        ]);
    }
}
