<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Penjualan\Application\Http\Controllers\PosCheckoutController;
use Modules\Penjualan\Application\Http\Controllers\PosShiftController;
use Modules\Penjualan\Application\Http\Controllers\SalesOrderController;

Route::prefix('api/penjualan')->middleware('api')->group(function () {
    // POS Retail Endpoints
    Route::get('pos/orders', [PosCheckoutController::class, 'index'])->name('penjualan.pos.orders.index');
    Route::post('pos/checkout', [PosCheckoutController::class, 'store'])->name('penjualan.pos.checkout');

    // POS Shift Drawer Endpoints
    Route::get('pos/shifts', [PosShiftController::class, 'index'])->name('penjualan.pos.shifts.index');
    Route::post('pos/shifts/open', [PosShiftController::class, 'open'])->name('penjualan.pos.shifts.open');
    Route::get('pos/shifts/{posShift}', [PosShiftController::class, 'show'])->name('penjualan.pos.shifts.show');
    Route::post('pos/shifts/{posShift}/close', [PosShiftController::class, 'close'])->name('penjualan.pos.shifts.close');
    Route::post('pos/shifts/{posShift}/reconcile', [PosShiftController::class, 'reconcile'])->name('penjualan.pos.shifts.reconcile');

    // B2B Sales Orders & Invoices
    Route::get('sales-orders', [SalesOrderController::class, 'index'])->name('penjualan.so.index');
    Route::post('sales-orders', [SalesOrderController::class, 'store'])->name('penjualan.so.store');
    Route::get('sales-orders/{salesOrder}', [SalesOrderController::class, 'show'])->name('penjualan.so.show');
    Route::post('sales-orders/{salesOrder}/confirm', [SalesOrderController::class, 'confirm'])->name('penjualan.so.confirm');
    Route::post('sales-orders/{salesOrder}/issue-invoice', [SalesOrderController::class, 'issueInvoice'])->name('penjualan.so.issue-invoice');

    Route::get('invoices', [SalesOrderController::class, 'invoices'])->name('penjualan.invoices.index');
    Route::get('invoices/{invoice}', [SalesOrderController::class, 'showInvoice'])->name('penjualan.invoices.show');
});
