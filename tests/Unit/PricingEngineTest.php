<?php

declare(strict_types=1);

namespace Tests\Unit;

use Modules\MasterData\Domain\Enums\TaxIdType;
use Modules\MasterData\Domain\Services\PricingEngine;
use PHPUnit\Framework\TestCase;

class PricingEngineTest extends TestCase
{
    private PricingEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new PricingEngine;
    }

    public function test_it_calculates_single_discount_and_coretax_floor_ppn(): void
    {
        // Harga: 100.000, Qty: 1, Diskon: 10%
        // DPP: 90.000
        // PPN 11%: floor(90.000 * 0.11) = 9.900
        // Net: 99.900
        $result = $this->engine->calculate(
            unitPrice: 100_000.0,
            quantity: 1,
            discounts: 10.0,
            isTaxable: true
        );

        $this->assertSame(100_000.0, $result->grossAmount);
        $this->assertSame(10_000.0, $result->discountAmount);
        $this->assertSame(90_000.0, $result->dpp);
        $this->assertSame(0.11, $result->ppnRate);
        $this->assertSame(9_900.0, $result->ppnAmount);
        $this->assertSame(99_900.0, $result->netAmount);
    }

    public function test_it_calculates_multi_tier_discounts_compound(): void
    {
        // Harga: 1.000.000, Diskon: "10+5%"
        // Tier 1: 10% dari 1.000.000 = 100.000 (sisa 900.000)
        // Tier 2: 5% dari 900.000 = 45.000 (sisa 855.000)
        // Total Diskon: 145.000
        // DPP: 855.000
        // PPN 11%: floor(855.000 * 0.11) = floor(94.050) = 94.050
        $result = $this->engine->calculate(
            unitPrice: 1_000_000.0,
            quantity: 1,
            discounts: '10+5%'
        );

        $this->assertSame(1_000_000.0, $result->grossAmount);
        $this->assertSame(145_000.0, $result->discountAmount);
        $this->assertSame(855_000.0, $result->dpp);
        $this->assertSame(94_050.0, $result->ppnAmount);
        $this->assertSame(949_050.0, $result->netAmount);
    }

    public function test_it_strictly_applies_floor_rounding_for_coretax_ppn(): void
    {
        // Contoh angka ganjil untuk menguji pembulatan ke bawah (floor):
        // Unit Price: 15.345, Qty: 1, Non-diskon
        // DPP: 15.345
        // 15.345 * 0.11 = 1.687,95
        // Pembulatan floor harus menghasilkan 1.687,0 (BUKAN 1.688)
        $result = $this->engine->calculate(
            unitPrice: 15_345.0,
            quantity: 1,
            discounts: 0.0,
            isTaxable: true
        );

        $this->assertSame(15_345.0, $result->dpp);
        $this->assertSame(1_687.0, $result->ppnAmount);
        $this->assertSame(17_032.0, $result->netAmount);
    }

    public function test_tax_id_type_validates_16_digits_for_coretax(): void
    {
        $npwpType = TaxIdType::NPWP_16;
        $nikType = TaxIdType::NIK;
        $nonWpType = TaxIdType::NON_WP;

        // Valid 16 digit
        $this->assertTrue($npwpType->isValidNumber('0123456789012345'));
        $this->assertTrue($nikType->isValidNumber('3201012345678901'));
        $this->assertTrue($nonWpType->isValidNumber(null));

        // Format dengan tanda titik/strip tetap valid jika jumlah digit angka 16
        $this->assertTrue($npwpType->isValidNumber('01.234.567.8-901.2345'));

        // Kurang dari 16 digit (misal NPWP 15 digit lama)
        $this->assertFalse($npwpType->isValidNumber('012345678901234'));
        $this->assertFalse($nikType->isValidNumber('12345'));
    }
}
