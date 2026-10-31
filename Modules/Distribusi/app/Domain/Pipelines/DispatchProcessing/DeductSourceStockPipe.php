<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Pipelines\DispatchProcessing;

use Closure;
use InvalidArgumentException;
use Modules\Distribusi\Domain\Models\StockTransfer;
use Modules\MasterData\Domain\Models\StockInventory;

class DeductSourceStockPipe
{
    /**
     * @param  array{transfer: StockTransfer, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $transfer = $payload['transfer'];

        foreach ($transfer->items as $item) {
            /** @var StockInventory|null $stock */
            $stock = StockInventory::query()
                ->where('warehouse_id', $transfer->from_warehouse_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->lockForUpdate()
                ->first();

            $available = $stock ? $stock->qty_on_hand : 0;
            if ($available < $item->qty_dispatched) {
                throw new InvalidArgumentException(
                    "Stok gudang asal tidak mencukupi untuk SKU varian #{$item->product_variant_id}. Tersedia: {$available}, Dibutuhkan: {$item->qty_dispatched}."
                );
            }

            $stock->decrement('qty_on_hand', $item->qty_dispatched);
        }

        return $next($payload);
    }
}
