<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Contexts;

final readonly class StockTransferDispatchContext
{
    /**
     * @param  array<int, array{product_variant_id: int, qty: int, notes?: string}>  $items
     * @param  array<string, mixed>  $snapshotPayload
     */
    public function __construct(
        public int $fromWarehouseId,
        public int $toWarehouseId,
        public array $items,
        public ?string $dispatchDate = null,
        public ?string $driverName = null,
        public ?string $vehiclePlate = null,
        public ?string $notes = null,
        public ?int $dispatchedBy = null,
        public array $snapshotPayload = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fromWarehouseId: (int) $data['from_warehouse_id'],
            toWarehouseId: (int) $data['to_warehouse_id'],
            items: (array) $data['items'],
            dispatchDate: isset($data['dispatch_date']) ? (string) $data['dispatch_date'] : null,
            driverName: isset($data['driver_name']) ? (string) $data['driver_name'] : null,
            vehiclePlate: isset($data['vehicle_plate']) ? (string) $data['vehicle_plate'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            dispatchedBy: isset($data['dispatched_by']) ? (int) $data['dispatched_by'] : null,
            snapshotPayload: (array) ($data['snapshot_payload'] ?? []),
        );
    }
}
