<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

class PricingEngine
{
    public const DEFAULT_PPN_RATE = 0.11; // PPN 11%

    /**
     * Hitung total transaksi dengan diskon bertingkat dan PPN 11% sesuai aturan DJP Coretax.
     *
     * @param  float  $unitPrice  Harga satuan barang
     * @param  float|int  $quantity  Jumlah barang
     * @param  array<int, float>|string|float  $discounts  Diskon: array bertingkat [10, 5], string "10+5", atau single float 10.0
     * @param  float  $fixedDiscountNominal  Potongan diskon langsung berupa nominal Rupiah
     * @param  bool  $isTaxable  Apakah kena PPN 11%
     * @param  float  $ppnRate  Tarif PPN (default 0.11)
     */
    public function calculate(
        float $unitPrice,
        float|int $quantity,
        array|string|float $discounts = 0.0,
        float $fixedDiscountNominal = 0.0,
        bool $isTaxable = true,
        float $ppnRate = self::DEFAULT_PPN_RATE
    ): PricingCalculationResult {
        $grossAmount = round($unitPrice * (float) $quantity, 2);

        // 1. Hitung Diskon Bertingkat
        $discountAmount = $this->calculateDiscountAmount($grossAmount, $discounts, $fixedDiscountNominal);

        // 2. Hitung DPP (Dasar Pengenaan Pajak)
        $dpp = max(0.0, round($grossAmount - $discountAmount, 2));

        // 3. Hitung PPN 11% dengan Pembulatan ke Bawah (floor) sesuai kaidah DJP Coretax
        $effectivePpnRate = $isTaxable ? $ppnRate : 0.0;
        $ppnAmount = $isTaxable ? floor($dpp * $effectivePpnRate) : 0.0;

        // 4. Hitung Net Amount (Total Tagihan Konsumen)
        $netAmount = round($dpp + $ppnAmount, 2);

        return new PricingCalculationResult(
            grossAmount: $grossAmount,
            discountAmount: $discountAmount,
            dpp: $dpp,
            ppnRate: $effectivePpnRate,
            ppnAmount: $ppnAmount,
            netAmount: $netAmount
        );
    }

    /**
     * Hitung akumulasi diskon (bertingkat + nominal).
     *
     * @param  float  $baseAmount  Nilai dasar sebelum diskon
     * @param  array<int, float>|string|float  $discounts  Diskon persentase
     * @param  float  $fixedDiscountNominal  Diskon nominal rupiah
     */
    public function calculateDiscountAmount(
        float $baseAmount,
        array|string|float $discounts,
        float $fixedDiscountNominal = 0.0
    ): float {
        $tiers = $this->parseDiscountTiers($discounts);
        $runningAmount = $baseAmount;
        $totalPercentageDiscount = 0.0;

        foreach ($tiers as $tierPercent) {
            if ($tierPercent <= 0.0) {
                continue;
            }

            $currentDiscount = round($runningAmount * ($tierPercent / 100.0), 2);
            $totalPercentageDiscount += $currentDiscount;
            $runningAmount -= $currentDiscount;
        }

        $totalDiscount = $totalPercentageDiscount + $fixedDiscountNominal;

        return min($baseAmount, max(0.0, round($totalDiscount, 2)));
    }

    /**
     * Parse representasi diskon menjadi array persentase urut.
     * Contoh: "10+5" => [10.0, 5.0]
     *
     * @param  array<int, float>|string|float  $discounts
     * @return array<int, float>
     */
    public function parseDiscountTiers(array|string|float $discounts): array
    {
        if (is_array($discounts)) {
            return array_map(fn ($d) => (float) $d, $discounts);
        }

        if (is_numeric($discounts)) {
            return [(float) $discounts];
        }

        if (is_string($discounts)) {
            $parts = explode('+', trim($discounts));
            $tiers = [];
            foreach ($parts as $part) {
                $trimmed = trim(str_replace('%', '', $part));
                if (is_numeric($trimmed)) {
                    $tiers[] = (float) $trimmed;
                }
            }

            return $tiers;
        }

        return [];
    }
}
