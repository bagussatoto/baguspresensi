<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Pipelines;

use Closure;
use InvalidArgumentException;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\Pembelian\Domain\Contexts\GoodsReceiptContext;
use Modules\Pembelian\Domain\Enums\PurchaseOrderStatus;
use Modules\Pembelian\Domain\Models\PurchaseOrder;
use Modules\Pembelian\Domain\Models\PurchaseOrderItem;

class ValidatePurchaseOrderPipe
{
    /**
     * @param  array{context: GoodsReceiptContext, data: array<string, mixed>}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $context = $payload['context'];

        $supplier = Supplier::findOrFail($context->supplierId);
        if (! $supplier->is_active) {
            throw new InvalidArgumentException("Pemasok [{$supplier->name}] tidak aktif.");
        }

        if ($context->purchaseOrderId !== null) {
            $po = PurchaseOrder::with('items')->findOrFail($context->purchaseOrderId);

            if (! in_array($po->status, [PurchaseOrderStatus::Approved, PurchaseOrderStatus::Partial], true)) {
                throw new InvalidArgumentException("Penerimaan barang hanya dapat diproses untuk PO berstatus Approved atau Partial. Status PO saat ini: {$po->status->label()}.");
            }

            foreach ($context->items as $itemData) {
                if (isset($itemData['purchase_order_item_id'])) {
                    /** @var PurchaseOrderItem|null $poItem */
                    $poItem = $po->items->firstWhere('id', $itemData['purchase_order_item_id']);
                    if ($poItem instanceof PurchaseOrderItem) {
                        $remaining = $poItem->remainingQty();
                        if ($itemData['qty_received'] > $remaining) {
                            throw new InvalidArgumentException("Qty penerimaan ({$itemData['qty_received']}) melebihi sisa PO ({$remaining}) untuk item #{$poItem->id}.");
                        }
                    }
                }
            }

            $payload['data']['purchase_order'] = $po;
        }

        return $next($payload);
    }
}
