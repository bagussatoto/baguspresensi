<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\MasterData\Application\Http\Controllers\CustomerController;
use Modules\MasterData\Application\Http\Controllers\MasterDataWebController;
use Modules\MasterData\Application\Http\Controllers\PricelistController;
use Modules\MasterData\Application\Http\Controllers\ProductVariantController;
use Modules\MasterData\Application\Http\Controllers\SupplierController;

Route::prefix('api/master-data')->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('variants', ProductVariantController::class);
    Route::apiResource('pricelists', PricelistController::class);
    Route::post('pricelists/{pricelist}/items', [PricelistController::class, 'addItem']);
    Route::post('simulate-pricing', [MasterDataWebController::class, 'simulatePricing'])->name('master-data.simulate-pricing');
});
