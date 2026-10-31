<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Pipelines\ReceiveProcessing;

use Closure;
use Modules\Distribusi\Domain\Models\StockTransfer;
use Modules\MasterData\Domain\Models\StockInventory;

class DeductInTransitStockPipe
{
    /**
     * @param  array{transfer: StockTransfer, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $transfer = $payload['transfer'];

        if ($transfer->in_transit_warehouse_id) {
            foreach ($transfer->items as $item) {
                $inTransitStock = StockInventory::query()
                    ->where('warehouse_id', $transfer->in_transit_warehouse_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($inTransitStock instanceof StockInventory) {
                    $deduct = min($inTransitStock->qty_on_hand, $item->qty_dispatched);
                    $inTransitStock->decrement('qty_on_hand', $deduct);
                }
            }
        }

        return $next($payload);
    }
}
