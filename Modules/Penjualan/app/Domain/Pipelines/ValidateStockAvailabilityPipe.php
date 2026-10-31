<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Pipelines;

use Closure;
use InvalidArgumentException;
use Modules\MasterData\Domain\Models\StockInventory;

class ValidateStockAvailabilityPipe
{
    /**
     * @param  array{warehouse_id: int, items: array<int, array{product_variant_id: int, qty: int, [key: string]: mixed}>, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $warehouseId = (int) $payload['warehouse_id'];

        foreach ($payload['items'] as $item) {
            $variantId = (int) $item['product_variant_id'];
            $requestedQty = (int) $item['qty'];

            /** @var StockInventory|null $stock */
            $stock = StockInventory::query()
                ->where('warehouse_id', $warehouseId)
                ->where('product_variant_id', $variantId)
                ->first();

            $available = $stock ? $stock->qty_on_hand : 0;
            if ($available < $requestedQty) {
                throw new InvalidArgumentException(
                    "Stok gudang tidak mencukupi untuk varian #{$variantId}. Tersedia: {$available}, Diminta: {$requestedQty}."
                );
            }
        }

        return $next($payload);
    }
}
