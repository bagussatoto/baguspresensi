<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

class PricingCalculationResult
{
    public function __construct(
        public readonly float $grossAmount,
        public readonly float $discountAmount,
        public readonly float $dpp,
        public readonly float $ppnRate,
        public readonly float $ppnAmount,
        public readonly float $netAmount,
    ) {}

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'gross_amount' => $this->grossAmount,
            'discount_amount' => $this->discountAmount,
            'dpp' => $this->dpp,
            'ppn_rate' => $this->ppnRate,
            'ppn_amount' => $this->ppnAmount,
            'net_amount' => $this->netAmount,
        ];
    }
}
