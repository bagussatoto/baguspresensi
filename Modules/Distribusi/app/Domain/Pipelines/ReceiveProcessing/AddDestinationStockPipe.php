<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Pipelines\ReceiveProcessing;

use Closure;
use Modules\Distribusi\Domain\Models\StockTransfer;
use Modules\MasterData\Domain\Models\StockInventory;

class AddDestinationStockPipe
{
    /**
     * @param  array{transfer: StockTransfer, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $transfer = $payload['transfer'];

        foreach ($transfer->items as $item) {
            /** @var StockInventory $destStock */
            $destStock = StockInventory::firstOrCreate(
                [
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'product_variant_id' => $item->product_variant_id,
                ],
                [
                    'qty_on_hand' => 0,
                    'safety_stock' => 0,
                ]
            );

            $destStock->increment('qty_on_hand', $item->qty_received);
        }

        return $next($payload);
    }
}
