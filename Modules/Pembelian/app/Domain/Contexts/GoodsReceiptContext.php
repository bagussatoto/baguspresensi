<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Contexts;

final readonly class GoodsReceiptContext
{
    /**
     * @param  array<int, array{purchase_order_item_id?: int, product_variant_id: int, qty_received: int, unit_cost: float, notes?: string}>  $items
     * @param  array<string, mixed>  $snapshotPayload
     */
    public function __construct(
        public int $supplierId,
        public int $warehouseId,
        public array $items,
        public ?int $purchaseOrderId = null,
        public ?string $deliveryNoteNumber = null,
        public ?string $receiptDate = null,
        public ?int $receivedBy = null,
        public ?string $notes = null,
        public bool $createBill = true,
        public array $snapshotPayload = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            supplierId: (int) $data['supplier_id'],
            warehouseId: (int) $data['warehouse_id'],
            items: (array) $data['items'],
            purchaseOrderId: isset($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null,
            deliveryNoteNumber: isset($data['delivery_note_number']) ? (string) $data['delivery_note_number'] : null,
            receiptDate: isset($data['receipt_date']) ? (string) $data['receipt_date'] : null,
            receivedBy: isset($data['received_by']) ? (int) $data['received_by'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            createBill: (bool) ($data['create_bill'] ?? true),
            snapshotPayload: (array) ($data['snapshot_payload'] ?? []),
        );
    }
}
