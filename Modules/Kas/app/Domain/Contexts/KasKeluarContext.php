<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Contexts;

use Modules\Kas\Domain\Enums\PaymentMethodType;

final readonly class KasKeluarContext
{
    /**
     * @param  array<int, array{reference_type?: string, reference_id?: int, allocation_amount: float, notes?: string}>  $lines
     * @param  array<string, mixed>  $snapshotPayload
     */
    public function __construct(
        public int $kasAccountId,
        public int $branchId,
        public PaymentMethodType $paymentMethod,
        public float $amount,
        public ?int $supplierId = null,
        public ?int $userId = null,
        public ?string $referenceNumber = null,
        public ?string $description = null,
        public ?string $transactionDate = null,
        public array $lines = [],
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
            kasAccountId: (int) $data['kas_account_id'],
            branchId: (int) $data['branch_id'],
            paymentMethod: $paymentMethod,
            amount: (float) $data['amount'],
            supplierId: isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            referenceNumber: isset($data['reference_number']) ? (string) $data['reference_number'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            transactionDate: isset($data['transaction_date']) ? (string) $data['transaction_date'] : null,
            lines: (array) ($data['lines'] ?? []),
            snapshotPayload: (array) ($data['snapshot_payload'] ?? []),
        );
    }
}
