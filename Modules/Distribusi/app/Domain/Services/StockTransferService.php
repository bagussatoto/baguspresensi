<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Services;

use Carbon\Carbon;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Distribusi\Domain\Contexts\StockTransferDispatchContext;
use Modules\Distribusi\Domain\Contexts\StockTransferReceiveContext;
use Modules\Distribusi\Domain\Enums\TransferStatus;
use Modules\Distribusi\Domain\Models\DeliveryOrder;
use Modules\Distribusi\Domain\Models\DeliveryOrderItem;
use Modules\Distribusi\Domain\Models\StockTransfer;
use Modules\Distribusi\Domain\Models\StockTransferItem;
use Modules\Distribusi\Domain\Pipelines\DispatchProcessing\AddInTransitStockPipe;
use Modules\Distribusi\Domain\Pipelines\DispatchProcessing\DeductSourceStockPipe;
use Modules\Distribusi\Domain\Pipelines\ReceiveProcessing\AddDestinationStockPipe;
use Modules\Distribusi\Domain\Pipelines\ReceiveProcessing\DeductInTransitStockPipe;
use Modules\Distribusi\Events\DeliveryDispatchedEvent;
use Modules\Distribusi\Events\TransferDispatchedEvent;
use Modules\Distribusi\Events\TransferReceivedEvent;
use Modules\MasterData\Domain\Models\StockInventory;

class StockTransferService
{
    /**
     * Memproses pengiriman barang antar-cabang (Step 1: Potong asal ➔ Masuk In-Transit).
     */
    public function dispatchTransfer(StockTransferDispatchContext $context): StockTransfer
    {
        if ($context->fromWarehouseId === $context->toWarehouseId) {
            throw new InvalidArgumentException('Gudang asal dan gudang tujuan transfer tidak boleh sama.');
        }

        if (empty($context->items)) {
            throw new InvalidArgumentException('Mutasi transfer harus memiliki minimal 1 item.');
        }

        return DB::transaction(function () use ($context) {
            $date = $context->dispatchDate ? Carbon::parse($context->dispatchDate) : Carbon::today();
            $transferNumber = $this->generateTransferNumber($date);

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $context->fromWarehouseId,
                'to_warehouse_id' => $context->toWarehouseId,
                'status' => TransferStatus::InTransit,
                'dispatch_date' => $date->toDateString(),
                'driver_name' => $context->driverName,
                'vehicle_plate' => $context->vehiclePlate,
                'notes' => $context->notes,
                'dispatched_by' => $context->dispatchedBy,
                'snapshot_payload' => $context->snapshotPayload ?: null,
            ]);

            $createdItems = [];
            foreach ($context->items as $itemData) {
                $qty = (int) $itemData['qty'];
                if ($qty <= 0) {
                    throw new InvalidArgumentException('Qty pengiriman transfer harus lebih besar dari 0.');
                }

                $createdItems[] = StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'qty_dispatched' => $qty,
                    'qty_received' => 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            $transfer->setRelation('items', collect($createdItems));

            // Jalankan pipa pemotongan fisik gudang asal dan penambahan ke in-transit
            app(Pipeline::class)
                ->send(['transfer' => $transfer])
                ->through([
                    DeductSourceStockPipe::class,
                    AddInTransitStockPipe::class,
                ])
                ->thenReturn();

            event(new TransferDispatchedEvent($transfer));

            return $transfer->fresh(['items.variant', 'fromWarehouse', 'toWarehouse', 'inTransitWarehouse']);
        });
    }

    /**
     * Memproses penerimaan fisik di cabang tujuan (Step 2: Potong In-Transit ➔ Tambah Fisik Tujuan).
     */
    public function receiveTransfer(StockTransferReceiveContext $context): StockTransfer
    {
        return DB::transaction(function () use ($context) {
            /** @var StockTransfer $transfer */
            $transfer = StockTransfer::with(['items', 'fromWarehouse', 'toWarehouse'])
                ->where('id', $context->transferId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transfer->status !== TransferStatus::InTransit) {
                throw new InvalidArgumentException("Hanya transfer berstatus In-Transit yang dapat diterima. Status saat ini: {$transfer->status->label()}.");
            }

            $date = $context->receivedDate ? Carbon::parse($context->receivedDate) : Carbon::today();
            $hasDiscrepancy = false;

            foreach ($context->items as $receivedData) {
                /** @var StockTransferItem|null $transferItem */
                $transferItem = $transfer->items->firstWhere('product_variant_id', $receivedData['product_variant_id']);
                if ($transferItem instanceof StockTransferItem) {
                    $qtyReceived = (int) $receivedData['qty_received'];
                    $transferItem->qty_received = $qtyReceived;
                    $transferItem->notes = $receivedData['notes'] ?? $transferItem->notes;
                    $transferItem->save();

                    if ($qtyReceived !== $transferItem->qty_dispatched) {
                        $hasDiscrepancy = true;
                    }
                }
            }

            $transfer->status = $hasDiscrepancy ? TransferStatus::Selisih : TransferStatus::Received;
            $transfer->received_date = $date->toDateString();
            $transfer->received_by = $context->receivedBy;
            if ($context->notes) {
                $transfer->notes = ($transfer->notes ? $transfer->notes."\n" : '').'Catatan Penerimaan: '.$context->notes;
            }
            $transfer->save();

            // Jalankan pipa pemotongan dari in-transit dan penambahan ke fisik tujuan
            app(Pipeline::class)
                ->send(['transfer' => $transfer])
                ->through([
                    DeductInTransitStockPipe::class,
                    AddDestinationStockPipe::class,
                ])
                ->thenReturn();

            event(new TransferReceivedEvent($transfer));

            return $transfer->fresh(['items.variant', 'fromWarehouse', 'toWarehouse']);
        });
    }

    /**
     * Menerbitkan Surat Jalan Pengiriman ke Konsumen (Delivery Order).
     */
    public function createDeliveryOrder(array $data): DeliveryOrder
    {
        if (empty($data['items'])) {
            throw new InvalidArgumentException('Surat jalan harus memiliki minimal 1 item.');
        }

        return DB::transaction(function () use ($data) {
            $date = isset($data['delivery_date']) ? Carbon::parse($data['delivery_date']) : Carbon::today();
            $prefix = sprintf('DO-%s-', $date->format('Ymd'));
            $count = DeliveryOrder::where('delivery_order_number', 'LIKE', "{$prefix}%")->count();
            $doNumber = sprintf('%s%04d', $prefix, $count + 1);

            $deliveryOrder = DeliveryOrder::create([
                'delivery_order_number' => $doNumber,
                'warehouse_id' => $data['warehouse_id'],
                'customer_id' => $data['customer_id'],
                'reference_number' => $data['reference_number'] ?? null,
                'status' => 'dispatched',
                'delivery_date' => $date->toDateString(),
                'recipient_name' => $data['recipient_name'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'vehicle_plate' => $data['vehicle_plate'] ?? null,
                'notes' => $data['notes'] ?? null,
                'dispatched_by' => $data['dispatched_by'] ?? null,
                'snapshot_payload' => $data['snapshot_payload'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $qty = (int) $itemData['qty'];
                DeliveryOrderItem::create([
                    'delivery_order_id' => $deliveryOrder->id,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'qty' => $qty,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Potong stok fisik gudang pengirim
                /** @var StockInventory|null $stock */
                $stock = StockInventory::query()
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->where('product_variant_id', $itemData['product_variant_id'])
                    ->lockForUpdate()
                    ->first();

                $available = $stock ? $stock->qty_on_hand : 0;
                if ($available < $qty) {
                    throw new InvalidArgumentException("Stok tidak mencukupi untuk pengiriman SKU #{$itemData['product_variant_id']}. Tersedia: {$available}, Diperlukan: {$qty}.");
                }

                $stock->decrement('qty_on_hand', $qty);
            }

            event(new DeliveryDispatchedEvent($deliveryOrder));

            return $deliveryOrder->fresh(['items.variant', 'customer', 'warehouse']);
        });
    }

    private function generateTransferNumber(Carbon $date): string
    {
        $prefix = sprintf('TRF-WH-%s-', $date->format('Ymd'));
        $count = StockTransfer::where('transfer_number', 'LIKE', "{$prefix}%")->count();

        return sprintf('%s%04d', $prefix, $count + 1);
    }
}
