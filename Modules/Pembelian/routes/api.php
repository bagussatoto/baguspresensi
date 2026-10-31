<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Pembelian\Application\Http\Controllers\GoodsReceiptController;
use Modules\Pembelian\Application\Http\Controllers\PurchaseBillController;
use Modules\Pembelian\Application\Http\Controllers\PurchaseOrderController;

Route::prefix('api/pembelian')->middleware('api')->group(function () {
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('pembelian.po.index');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('pembelian.po.store');
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('pembelian.po.show');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('pembelian.po.approve');

    Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->name('pembelian.receipts.index');
    Route::post('goods-receipts', [GoodsReceiptController::class, 'store'])->name('pembelian.receipts.store');
    Route::get('goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->name('pembelian.receipts.show');

    Route::get('bills', [PurchaseBillController::class, 'index'])->name('pembelian.bills.index');
    Route::get('bills/{bill}', [PurchaseBillController::class, 'show'])->name('pembelian.bills.show');
});
