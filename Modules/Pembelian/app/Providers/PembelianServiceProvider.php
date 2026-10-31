<?php

declare(strict_types=1);

namespace Modules\Pembelian\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Pembelian\Domain\Services\PurchaseOrderOrchestrator;

class PembelianServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PurchaseOrderOrchestrator::class, function () {
            return new PurchaseOrderOrchestrator;
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
