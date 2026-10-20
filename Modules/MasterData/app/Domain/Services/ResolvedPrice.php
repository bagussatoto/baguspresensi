<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

class ResolvedPrice
{
    public function __construct(
        public readonly float $unitPrice,
        public readonly float $discountPercent,
        public readonly string $source,
        public readonly ?int $pricelistId = null,
        public readonly ?int $pricelistItemId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'unit_price' => $this->unitPrice,
            'discount_percent' => $this->discountPercent,
            'source' => $this->source,
            'pricelist_id' => $this->pricelistId,
            'pricelist_item_id' => $this->pricelistItemId,
        ];
    }
}
