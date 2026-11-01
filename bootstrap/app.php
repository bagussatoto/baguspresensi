<?php

use App\Http\Middleware\EnforceAuditorReadOnly;
use App\Http\Middleware\MockTimeMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auditor.readonly' => EnforceAuditorReadOnly::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'presensi2/clock-in',
            'presensi2/clock-out',
            'presensi2/sync-offline',
            'bagus/presensi2/clock-in',
            'bagus/presensi2/clock-out',
        ]);
        $middleware->web(append: [
            MockTimeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
