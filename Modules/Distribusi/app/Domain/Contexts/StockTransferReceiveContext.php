<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Contexts;

final readonly class StockTransferReceiveContext
{
    /**
     * @param  array<int, array{product_variant_id: int, qty_received: int, notes?: string}>  $items
     */
    public function __construct(
        public int $transferId,
        public array $items,
        public ?string $receivedDate = null,
        public ?int $receivedBy = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transferId: (int) $data['transfer_id'],
            items: (array) $data['items'],
            receivedDate: isset($data['received_date']) ? (string) $data['received_date'] : null,
            receivedBy: isset($data['received_by']) ? (int) $data['received_by'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
