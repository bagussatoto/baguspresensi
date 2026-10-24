<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Attendance\Services\AttendanceSettingService;
use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $limit = (int) app(AttendanceSettingService::class)
                ->get('api.rate_limit_per_minute', 60);

            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('admin.dashboard.layout', function ($view) {
            if (! array_key_exists('profileData', $view->getData())) {
                $view->with('profileData', AdminDashboardController::getProfileData(request()));
            }
        });
    }
}
