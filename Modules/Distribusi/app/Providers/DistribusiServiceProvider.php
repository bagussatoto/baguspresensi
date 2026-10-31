<?php

declare(strict_types=1);

namespace Modules\Distribusi\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Distribusi\Domain\Services\StockTransferService;

class DistribusiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StockTransferService::class, function () {
            return new StockTransferService;
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
