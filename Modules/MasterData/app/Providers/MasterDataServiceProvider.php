<?php

declare(strict_types=1);

namespace Modules\MasterData\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\MasterData\Domain\Services\CustomerCreditGuard;
use Modules\MasterData\Domain\Services\PriceResolverEngine;
use Modules\MasterData\Domain\Services\PricingEngine;

class MasterDataServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PricingEngine::class, function () {
            return new PricingEngine;
        });

        $this->app->singleton(PriceResolverEngine::class, function () {
            return new PriceResolverEngine;
        });

        $this->app->singleton(CustomerCreditGuard::class, function () {
            return new CustomerCreditGuard;
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
