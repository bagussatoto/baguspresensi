<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Services;

use Carbon\Carbon;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\Pembelian\Domain\Contexts\GoodsReceiptContext;
use Modules\Pembelian\Domain\Contexts\PurchaseOrderContext;
use Modules\Pembelian\Domain\Enums\PurchaseBillStatus;
use Modules\Pembelian\Domain\Enums\PurchaseOrderStatus;
use Modules\Pembelian\Domain\Models\GoodsReceipt;
use Modules\Pembelian\Domain\Models\GoodsReceiptItem;
use Modules\Pembelian\Domain\Models\PurchaseBill;
use Modules\Pembelian\Domain\Models\PurchaseBillItem;
use Modules\Pembelian\Domain\Models\PurchaseOrder;
use Modules\Pembelian\Domain\Models\PurchaseOrderItem;
use Modules\Pembelian\Domain\Pipelines\IncrementWarehouseStockPipe;
use Modules\Pembelian\Domain\Pipelines\RecalculateMovingAverageHppPipe;
use Modules\Pembelian\Domain\Pipelines\ValidatePurchaseOrderPipe;
use Modules\Pembelian\Events\GoodsReceivedEvent;
use Modules\Pembelian\Events\PurchaseBillIssuedEvent;

class PurchaseOrderOrchestrator
{
    /**
     * Membuat draft Purchase Order baru.
     */
    public function createPurchaseOrder(PurchaseOrderContext $context): PurchaseOrder
    {
        if (empty($context->items)) {
            throw new InvalidArgumentException('Purchase Order harus memiliki minimal 1 item.');
        }

        return DB::transaction(function () use ($context) {
            $date = $context->orderDate ? Carbon::parse($context->orderDate) : Carbon::today();
            $poNumber = $this->generatePoNumber($context->branchId, $date);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $context->supplierId,
                'branch_id' => $context->branchId,
                'status' => PurchaseOrderStatus::Draft,
                'order_date' => $date->toDateString(),
                'expected_delivery_date' => $context->expectedDeliveryDate,
                'notes' => $context->notes,
                'created_by' => $context->createdBy,
            ]);

            $totalAmount = 0.0;
            foreach ($context->items as $itemData) {
                $qty = (int) $itemData['qty_ordered'];
                $price = (float) $itemData['unit_price'];
                $subtotal = $qty * $price;
                $totalAmount += $subtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'qty_ordered' => $qty,
                    'qty_received' => 0,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            $po->total_amount = $totalAmount;
            $po->net_amount = $totalAmount + (float) $po->tax_amount;
            $po->save();

            return $po->fresh(['items.variant', 'supplier', 'branch']);
        });
    }

    /**
     * Menyetujui PO untuk dapat dikirim oleh supplier.
     */
    public function approvePurchaseOrder(PurchaseOrder $po, int $approvedBy): PurchaseOrder
    {
        if ($po->status !== PurchaseOrderStatus::Draft) {
            throw new InvalidArgumentException("Hanya PO berstatus Draft yang dapat disetujui. Status saat ini: {$po->status->label()}.");
        }

        $po->status = PurchaseOrderStatus::Approved;
        $po->approved_by = $approvedBy;
        $po->save();

        return $po->fresh();
    }

    /**
     * Menerima barang fisik di gudang, update stok, kalkulasi HPP bergerak, dan terbitkan tagihan hutang.
     */
    public function receiveGoods(GoodsReceiptContext $context): GoodsReceipt
    {
        if (empty($context->items)) {
            throw new InvalidArgumentException('Penerimaan barang harus memiliki minimal 1 item.');
        }

        // Jalankan validasi pra-penerimaan
        $pipelineData = app(Pipeline::class)
            ->send(['context' => $context, 'data' => []])
            ->through([
                ValidatePurchaseOrderPipe::class,
            ])
            ->thenReturn();

        return DB::transaction(function () use ($context, $pipelineData) {
            $date = $context->receiptDate ? Carbon::parse($context->receiptDate) : Carbon::today();
            $receiptNumber = $this->generateReceiptNumber($date);

            $receipt = GoodsReceipt::create([
                'receipt_number' => $receiptNumber,
                'purchase_order_id' => $context->purchaseOrderId,
                'supplier_id' => $context->supplierId,
                'warehouse_id' => $context->warehouseId,
                'delivery_note_number' => $context->deliveryNoteNumber,
                'receipt_date' => $date->toDateString(),
                'received_by' => $context->receivedBy,
                'notes' => $context->notes,
                'snapshot_payload' => $context->snapshotPayload ?: null,
            ]);

            $totalBillAmount = 0.0;
            $createdItems = [];

            foreach ($context->items as $itemData) {
                $item = GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'] ?? null,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'qty_received' => (int) $itemData['qty_received'],
                    'unit_cost' => (float) $itemData['unit_cost'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalBillAmount += $item->totalCost();
                $createdItems[] = $item;

                // Update qty_received di PO jika ditautkan ke PO
                if (isset($itemData['purchase_order_item_id'])) {
                    /** @var PurchaseOrderItem|null $poItem */
                    $poItem = PurchaseOrderItem::find($itemData['purchase_order_item_id']);
                    if ($poItem instanceof PurchaseOrderItem) {
                        $poItem->increment('qty_received', $item->qty_received);
                    }
                }
            }

            // Update status PO jika relevan
            if ($context->purchaseOrderId !== null) {
                $po = PurchaseOrder::with('items')->find($context->purchaseOrderId);
                if ($po instanceof PurchaseOrder) {
                    $allFullyReceived = $po->items->every(fn (PurchaseOrderItem $item) => $item->qty_received >= $item->qty_ordered);
                    $po->status = $allFullyReceived ? PurchaseOrderStatus::Closed : PurchaseOrderStatus::Partial;
                    $po->save();
                }
            }

            $receipt->setRelation('items', collect($createdItems));

            // Jalankan pipa penambahan stok fisik & kalkulasi HPP bergerak
            app(Pipeline::class)
                ->send(['context' => $context, 'receipt' => $receipt, 'data' => $pipelineData['data']])
                ->through([
                    IncrementWarehouseStockPipe::class,
                    RecalculateMovingAverageHppPipe::class,
                ])
                ->thenReturn();

            // Terbitkan tagihan hutang pembelian (AP) jika opsi createBill aktif
            if ($context->createBill) {
                $supplier = Supplier::find($context->supplierId);
                $termDays = $supplier ? (int) $supplier->payment_term_days : 0;
                $dueDate = $date->copy()->addDays($termDays);

                $bill = PurchaseBill::create([
                    'bill_number' => $this->generateBillNumber($date),
                    'purchase_order_id' => $context->purchaseOrderId,
                    'supplier_id' => $context->supplierId,
                    'status' => PurchaseBillStatus::Unpaid,
                    'bill_date' => $date->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'total_amount' => $totalBillAmount,
                    'paid_amount' => 0.00,
                    'remaining_balance' => $totalBillAmount,
                    'notes' => "Tagihan otomatis atas penerimaan barang #{$receiptNumber}",
                ]);

                foreach ($createdItems as $grItem) {
                    PurchaseBillItem::create([
                        'purchase_bill_id' => $bill->id,
                        'goods_receipt_item_id' => $grItem->id,
                        'product_variant_id' => $grItem->product_variant_id,
                        'qty' => $grItem->qty_received,
                        'unit_price' => $grItem->unit_cost,
                        'subtotal' => $grItem->totalCost(),
                    ]);
                }

                event(new PurchaseBillIssuedEvent($bill));
            }

            event(new GoodsReceivedEvent($receipt));

            return $receipt->fresh(['items.variant', 'supplier', 'warehouse']);
        });
    }

    private function generatePoNumber(int $branchId, Carbon $date): string
    {
        $prefix = sprintf('PO-%03d-%s-', $branchId, $date->format('Ymd'));
        $count = PurchaseOrder::where('po_number', 'LIKE', "{$prefix}%")->count();

        return sprintf('%s%04d', $prefix, $count + 1);
    }

    private function generateReceiptNumber(Carbon $date): string
    {
        $prefix = sprintf('GR-%s-', $date->format('Ymd'));
        $count = GoodsReceipt::where('receipt_number', 'LIKE', "{$prefix}%")->count();

        return sprintf('%s%04d', $prefix, $count + 1);
    }

    private function generateBillNumber(Carbon $date): string
    {
        $prefix = sprintf('BILL-%s-', $date->format('Ymd'));
        $count = PurchaseBill::where('bill_number', 'LIKE', "{$prefix}%")->count();

        return sprintf('%s%04d', $prefix, $count + 1);
    }
}
