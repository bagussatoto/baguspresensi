<?php

declare(strict_types=1);

namespace Modules\Kas\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Kas\Domain\Services\KasKeluarService;
use Modules\Kas\Domain\Services\KasMasukService;
use Modules\Kas\Domain\Services\KasMutasiService;
use Modules\MasterData\Domain\Services\CustomerCreditGuard;

class KasServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(KasMasukService::class, function ($app) {
            return new KasMasukService($app->make(CustomerCreditGuard::class));
        });

        $this->app->singleton(KasKeluarService::class, function () {
            return new KasKeluarService;
        });

        $this->app->singleton(KasMutasiService::class, function () {
            return new KasMutasiService;
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
