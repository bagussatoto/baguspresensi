<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Contexts;

final readonly class KasMutasiContext
{
    public function __construct(
        public int $fromKasAccountId,
        public int $toKasAccountId,
        public float $amount,
        public float $adminFee = 0.0,
        public ?int $authorizedBy = null,
        public ?string $referenceNumber = null,
        public ?string $description = null,
        public ?string $transferDate = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fromKasAccountId: (int) $data['from_kas_account_id'],
            toKasAccountId: (int) $data['to_kas_account_id'],
            amount: (float) $data['amount'],
            adminFee: isset($data['admin_fee']) ? (float) $data['admin_fee'] : 0.0,
            authorizedBy: isset($data['authorized_by']) ? (int) $data['authorized_by'] : null,
            referenceNumber: isset($data['reference_number']) ? (string) $data['reference_number'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            transferDate: isset($data['transfer_date']) ? (string) $data['transfer_date'] : null,
        );
    }
}
