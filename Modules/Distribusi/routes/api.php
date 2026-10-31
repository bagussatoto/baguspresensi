<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Distribusi\Application\Http\Controllers\DeliveryOrderController;
use Modules\Distribusi\Application\Http\Controllers\StockTransferController;

Route::prefix('api/distribusi')->middleware('api')->group(function () {
    Route::get('stock-transfers', [StockTransferController::class, 'index'])->name('distribusi.transfers.index');
    Route::post('stock-transfers', [StockTransferController::class, 'store'])->name('distribusi.transfers.store');
    Route::get('stock-transfers/{stockTransfer}', [StockTransferController::class, 'show'])->name('distribusi.transfers.show');
    Route::post('stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])->name('distribusi.transfers.receive');

    Route::get('delivery-orders', [DeliveryOrderController::class, 'index'])->name('distribusi.delivery-orders.index');
    Route::post('delivery-orders', [DeliveryOrderController::class, 'store'])->name('distribusi.delivery-orders.store');
    Route::get('delivery-orders/{deliveryOrder}', [DeliveryOrderController::class, 'show'])->name('distribusi.delivery-orders.show');
});
