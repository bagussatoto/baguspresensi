<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Contexts;

final readonly class SalesOrderContext
{
    /**
     * @param  array<int, array{product_variant_id: int, qty: int, unit_price?: float, discount_amount?: float, notes?: string}>  $items
     * @param  array<string, mixed>  $snapshotPayload
     */
    public function __construct(
        public int $customerId,
        public int $branchId,
        public int $warehouseId,
        public array $items,
        public ?string $orderDate = null,
        public ?int $paymentTermDays = null,
        public ?string $notes = null,
        public ?int $createdBy = null,
        public array $snapshotPayload = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            customerId: (int) $data['customer_id'],
            branchId: (int) $data['branch_id'],
            warehouseId: (int) $data['warehouse_id'],
            items: (array) $data['items'],
            orderDate: isset($data['order_date']) ? (string) $data['order_date'] : null,
            paymentTermDays: isset($data['payment_term_days']) ? (int) $data['payment_term_days'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
            snapshotPayload: (array) ($data['snapshot_payload'] ?? []),
        );
    }
}
