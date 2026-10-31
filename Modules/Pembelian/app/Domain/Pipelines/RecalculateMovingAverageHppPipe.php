<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Pipelines;

use Closure;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\Pembelian\Domain\Models\GoodsReceipt;

class RecalculateMovingAverageHppPipe
{
    /**
     * Hitung HPP Bergerak (Moving Average HPP) sesuai PSAK 14.
     *
     * Rumus:
     * New HPP = ((Stok Sebelumnya * HPP Lama) + (Qty Masuk * Harga Beli)) / (Stok Sebelumnya + Qty Masuk)
     *
     * @param  array{receipt: GoodsReceipt, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $receipt = $payload['receipt'];

        foreach ($receipt->items as $item) {
            $variant = ProductVariant::find($item->product_variant_id);
            if (! $variant instanceof ProductVariant) {
                continue;
            }

            // Total stok saat ini (sudah ditambah oleh IncrementWarehouseStockPipe)
            $currentTotalStock = (int) StockInventory::where('product_variant_id', $variant->id)->sum('qty_on_hand');
            $incomingQty = (int) $item->qty_received;
            $incomingCost = (float) $item->unit_cost;

            // Stok sebelumnya sebelum penerimaan ini
            $previousStock = max(0, $currentTotalStock - $incomingQty);
            $previousHpp = (float) $variant->cost_price;

            if ($currentTotalStock > 0) {
                $newHpp = (($previousStock * $previousHpp) + ($incomingQty * $incomingCost)) / $currentTotalStock;
                $variant->cost_price = round($newHpp, 2);
                $variant->save();
            }
        }

        return $next($payload);
    }
}
