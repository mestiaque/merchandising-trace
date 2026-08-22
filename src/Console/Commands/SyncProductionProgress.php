<?php

namespace ME\MerchandisingTrace\Console\Commands;

use Illuminate\Console\Command;
use ME\MerchandisingTrace\Services\ProductionProgressSyncService;

class SyncProductionProgress extends Command
{
    protected $signature = 'merchandising-trace:sync-production-progress';
    protected $description = 'Mirror production plan-line-size rollups into the merchandiser progress read-model';

    public function handle(ProductionProgressSyncService $sync): int
    {
        $count = $sync->syncAll();
        $this->info("Synced production progress for {$count} PO(s).");

        return self::SUCCESS;
    }
}
