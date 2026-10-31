<?php

declare(strict_types=1);

namespace Modules\Penjualan\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Kas\Domain\Services\KasMasukService;
use Modules\MasterData\Domain\Services\CustomerCreditGuard;
use Modules\Penjualan\Domain\Services\PosShiftService;
use Modules\Penjualan\Domain\Services\SalesOrderOrchestrator;

class PenjualanServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PosShiftService::class, function () {
            return new PosShiftService;
        });

        $this->app->singleton(SalesOrderOrchestrator::class, function ($app) {
            return new SalesOrderOrchestrator(
                $app->make(KasMasukService::class),
                $app->make(CustomerCreditGuard::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        if (file_exists(__DIR__.'/../../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        }
    }
}
