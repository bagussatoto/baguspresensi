<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Pipelines\DispatchProcessing;

use Closure;
use Modules\Distribusi\Domain\Models\StockTransfer;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;

class AddInTransitStockPipe
{
    /**
     * @param  array{transfer: StockTransfer, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $transfer = $payload['transfer'];

        $inTransitWarehouse = Warehouse::firstOrCreate(
            [
                'branch_id' => $transfer->fromWarehouse->branch_id,
                'type' => 'in_transit',
            ],
            [
                'code' => sprintf('WH-TRF-BR%03d', $transfer->fromWarehouse->branch_id),
                'name' => "In-Transit Gudang Cabang #{$transfer->fromWarehouse->branch_id}",
                'is_active' => true,
            ]
        );

        $transfer->in_transit_warehouse_id = $inTransitWarehouse->id;
        $transfer->save();

        foreach ($transfer->items as $item) {
            /** @var StockInventory $inTransitStock */
            $inTransitStock = StockInventory::firstOrCreate(
                [
                    'warehouse_id' => $inTransitWarehouse->id,
                    'product_variant_id' => $item->product_variant_id,
                ],
                [
                    'qty_on_hand' => 0,
                    'safety_stock' => 0,
                ]
            );

            $inTransitStock->increment('qty_on_hand', $item->qty_dispatched);
        }

        return $next($payload);
    }
}
