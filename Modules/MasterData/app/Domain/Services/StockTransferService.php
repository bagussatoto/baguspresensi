<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

use Illuminate\Support\Facades\DB;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\StockTransferDispatch;

/**
 * Domain Service: Menangani distribusi & mutasi stok antar gudang / kasir.
 */
class StockTransferService
{
    /**
     * Catat dan eksekusi transfer stok barang.
     */
    public function transfer(
        int $fromWarehouseId,
        int $toWarehouseId,
        int $variantId,
        int $qty,
        ?string $operatorNotes = null
    ): StockTransferDispatch {
        return DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $variantId, $qty, $operatorNotes) {
            $transfer = StockTransferDispatch::create([
                'transfer_code' => 'TRF-'.date('YmdHis').'-'.rand(10, 99),
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'product_variant_id' => $variantId,
                'qty' => $qty,
                'status' => 'received',
                'dispatched_at' => now(),
                'received_at' => now(),
                'operator_notes' => $operatorNotes ?? 'Distribusi mutasi stok internal Maya Graha Kencana.',
            ]);

            // Mutasi kuantiti keluar di gudang asal
            $fromStock = StockInventory::firstOrCreate(
                ['warehouse_id' => $fromWarehouseId, 'product_variant_id' => $variantId],
                ['qty_on_hand' => 0]
            );
            $fromStock->decrement('qty_on_hand', $qty);

            // Mutasi kuantiti masuk di gudang/pos tujuan
            $toStock = StockInventory::firstOrCreate(
                ['warehouse_id' => $toWarehouseId, 'product_variant_id' => $variantId],
                ['qty_on_hand' => 0]
            );
            $toStock->increment('qty_on_hand', $qty);

            return $transfer;
        });
    }
}
