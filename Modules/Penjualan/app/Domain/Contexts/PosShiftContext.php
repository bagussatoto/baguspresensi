<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Contexts;

final readonly class PosShiftContext
{
    public function __construct(
        public int $branchId,
        public int $kasAccountId,
        public int $cashierId,
        public float $openingCash = 0.0,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            branchId: (int) $data['branch_id'],
            kasAccountId: (int) $data['kas_account_id'],
            cashierId: (int) $data['cashier_id'],
            openingCash: (float) ($data['opening_cash'] ?? 0.0),
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
