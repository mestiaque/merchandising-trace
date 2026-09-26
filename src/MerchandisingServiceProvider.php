<?php

namespace ME\MerchandisingTrace;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MerchandisingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'merchandising-trace');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'merchandising-trace');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->publishes([__DIR__ . '/public' => public_path('/')], 'merchandising-trace-assets');

        $this->mergeSidebar();
        $this->mergePermissions();
        $this->registerApprovalModules();

        // Summernote HTML fields: @richtext($model->remarks) echoes sanitized HTML.
        Blade::directive('richtext', fn (string $expression) => "<?php echo \\ME\\MerchandisingTrace\\Support\\RichText::clean({$expression}); ?>");

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\RunTnaDailyJobs::class,
                Console\Commands\SyncProductionProgress::class,
            ]);
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command(Console\Commands\RunTnaDailyJobs::class)->dailyAt('01:00');
            $schedule->command(Console\Commands\SyncProductionProgress::class)->hourly();
        });
    }

    public function register(): void
    {
        if (file_exists(__DIR__ . '/Config/config.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'merchandising-trace');
        }

        if (file_exists(__DIR__ . '/Config/master_excel.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/master_excel.php', 'merchandising-trace-master-excel');
        }

        $this->app->singleton(Services\DocumentNumberService::class);
    }

    private function mergeSidebar(): void
    {
        if (! file_exists($sidebar = __DIR__ . '/Config/sidebar.php')) {
            return;
        }

        // Sidebar entries are a numeric array — must array_merge, not mergeConfigFrom.
        Config::set('sidebar', array_merge(
            config('sidebar', []),
            require $sidebar
        ));
    }

    /**
     * Buyers / suppliers go through the host's central Approvals page. The
     * handlers are registered here (not in the host's config/approval.php)
     * so the package brings its own approval modules along.
     */
    private function registerApprovalModules(): void
    {
        if (! interface_exists(\App\Contracts\ApprovalHandlerInterface::class)) {
            return;
        }

        Config::set('approval.modules', array_merge([
            Models\Buyer::APPROVAL_MODULE => Approvals\MasterApprovalHandler::class,
            Models\Supplier::APPROVAL_MODULE => Approvals\MasterApprovalHandler::class,
            Services\SampleApprovalService::MODULE => Approvals\SampleApprovalHandler::class,
        ], config('approval.modules', [])));
    }

    private function mergePermissions(): void
    {
        if (! file_exists($file = __DIR__ . '/Config/permission.php')) {
            return;
        }

        $merchPermissions = require $file;
        $main = config('permission', []);
        $main['modules'] = $main['modules'] ?? [];

        // The host's config/permission.php nests every group under a top-level
        // 'modules' key — both the Roles Setup screen and hasChildPermission()/
        // hasPermission() read it from there.
        foreach ($merchPermissions as $group => $modules) {
            $main['modules'][$group] = $modules;
        }

        Config::set('permission', $main);
    }
}
