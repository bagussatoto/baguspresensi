<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Contexts;

final readonly class PurchaseOrderContext
{
    /**
     * @param  array<int, array{product_variant_id: int, qty_ordered: int, unit_price: float, notes?: string}>  $items
     */
    public function __construct(
        public int $supplierId,
        public int $branchId,
        public array $items,
        public ?string $orderDate = null,
        public ?string $expectedDeliveryDate = null,
        public ?string $notes = null,
        public ?int $createdBy = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            supplierId: (int) $data['supplier_id'],
            branchId: (int) $data['branch_id'],
            items: (array) $data['items'],
            orderDate: isset($data['order_date']) ? (string) $data['order_date'] : null,
            expectedDeliveryDate: isset($data['expected_delivery_date']) ? (string) $data['expected_delivery_date'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
        );
    }
}
