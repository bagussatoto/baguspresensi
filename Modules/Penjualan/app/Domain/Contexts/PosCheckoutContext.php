<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Contexts;

use Modules\Kas\Domain\Enums\PaymentMethodType;

final readonly class PosCheckoutContext
{
    /**
     * @param  array<int, array{product_variant_id: int, qty: int, unit_price?: float, discount_amount?: float}>  $items
     * @param  array<string, mixed>  $snapshotPayload
     */
    public function __construct(
        public int $branchId,
        public int $warehouseId,
        public PaymentMethodType $paymentMethod,
        public float $cashPaid,
        public array $items,
        public ?int $posShiftId = null,
        public ?int $customerId = null,
        public ?int $cashierId = null,
        public ?string $notes = null,
        public array $snapshotPayload = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $paymentMethod = $data['payment_method'] instanceof PaymentMethodType
            ? $data['payment_method']
            : PaymentMethodType::from((string) ($data['payment_method'] ?? 'cash'));

        return new self(
            branchId: (int) $data['branch_id'],
            warehouseId: (int) $data['warehouse_id'],
            paymentMethod: $paymentMethod,
            cashPaid: (float) ($data['cash_paid'] ?? 0.0),
            items: (array) $data['items'],
            posShiftId: isset($data['pos_shift_id']) ? (int) $data['pos_shift_id'] : null,
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            cashierId: isset($data['cashier_id']) ? (int) $data['cashier_id'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            snapshotPayload: (array) ($data['snapshot_payload'] ?? []),
        );
    }
}
