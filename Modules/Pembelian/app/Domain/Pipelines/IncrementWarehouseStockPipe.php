<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Pipelines;

use Closure;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\Pembelian\Domain\Contexts\GoodsReceiptContext;
use Modules\Pembelian\Domain\Models\GoodsReceipt;

class IncrementWarehouseStockPipe
{
    /**
     * @param  array{context: GoodsReceiptContext, data: array<string, mixed>, receipt: GoodsReceipt}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $context = $payload['context'];
        $receipt = $payload['receipt'];

        foreach ($receipt->items as $item) {
            /** @var StockInventory $stock */
            $stock = StockInventory::firstOrCreate(
                [
                    'warehouse_id' => $context->warehouseId,
                    'product_variant_id' => $item->product_variant_id,
                ],
                [
                    'qty_on_hand' => 0,
                    'safety_stock' => 0,
                ]
            );

            $stock->increment('qty_on_hand', $item->qty_received);
        }

        return $next($payload);
    }
}
