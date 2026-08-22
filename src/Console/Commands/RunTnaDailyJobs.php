<?php

namespace ME\MerchandisingTrace\Console\Commands;

use Illuminate\Console\Command;
use ME\MerchandisingTrace\Models\TnaPlan;
use ME\MerchandisingTrace\Services\PcdGateService;
use ME\MerchandisingTrace\Services\TnaAlertService;

/**
 * §8.5 "nightly job" + §8.6 "daily job" — re-evaluates PCD for every open
 * plan, then raises due-soon/overdue/blocked-PCD alerts.
 */
class RunTnaDailyJobs extends Command
{
    protected $signature = 'merchandising-trace:tna-daily';
    protected $description = 'Re-evaluate PCD gates and raise T&A alerts (§8.5/§8.6)';

    public function handle(PcdGateService $gate, TnaAlertService $alerts): int
    {
        $evaluated = 0;
        TnaPlan::query()->where('pcd_result', '!=', 'pass')->chunkById(200, function ($plans) use ($gate, &$evaluated) {
            foreach ($plans as $plan) {
                $gate->evaluate($plan);
                $evaluated++;
            }
        });
        $this->info("PCD re-evaluated for {$evaluated} plan(s).");

        $created = $alerts->runDaily();
        $this->info("{$created} new alert(s) raised.");

        return self::SUCCESS;
    }
}
