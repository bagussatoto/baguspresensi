<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Kas\Application\Http\Controllers\KasAccountController;
use Modules\Kas\Application\Http\Controllers\KasKeluarController;
use Modules\Kas\Application\Http\Controllers\KasMasukController;
use Modules\Kas\Application\Http\Controllers\KasMutasiController;

Route::prefix('api/kas')->middleware('api')->group(function () {
    Route::get('accounts', [KasAccountController::class, 'index'])->name('kas.accounts.index');
    Route::post('accounts', [KasAccountController::class, 'store'])->name('kas.accounts.store');
    Route::get('accounts/{account}', [KasAccountController::class, 'show'])->name('kas.accounts.show');

    Route::get('kas-masuk', [KasMasukController::class, 'index'])->name('kas.masuk.index');
    Route::post('kas-masuk', [KasMasukController::class, 'store'])->name('kas.masuk.store');

    Route::get('kas-keluar', [KasKeluarController::class, 'index'])->name('kas.keluar.index');
    Route::post('kas-keluar', [KasKeluarController::class, 'store'])->name('kas.keluar.store');

    Route::get('kas-mutasi', [KasMutasiController::class, 'index'])->name('kas.mutasi.index');
    Route::post('kas-mutasi', [KasMutasiController::class, 'store'])->name('kas.mutasi.store');
});
