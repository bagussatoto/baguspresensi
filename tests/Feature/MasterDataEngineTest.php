<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Enums\TaxIdType;
use Modules\MasterData\Domain\Exceptions\CreditLimitExceededException;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Pricelist;
use Modules\MasterData\Domain\Models\PricelistItem;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Services\CustomerCreditGuard;
use Modules\MasterData\Domain\Services\PriceResolverEngine;
use Tests\TestCase;

class MasterDataEngineTest extends TestCase
{
    use RefreshDatabase;

    private PriceResolverEngine $resolver;

    private CustomerCreditGuard $creditGuard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(PriceResolverEngine::class);
        $this->creditGuard = app(CustomerCreditGuard::class);
    }

    public function test_price_resolver_resolves_tier_pricing_by_quantity(): void
    {
        $product = Product::create([
            'code' => 'PRD-001',
            'name' => 'Semen Gresik 50kg',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SMN-GRS-50KG',
            'name' => 'Semen Gresik 50kg Standard',
            'base_price' => 65_000.0,
            'cost_price' => 55_000.0,
        ]);

        $grosirPricelist = Pricelist::create([
            'code' => 'PL-GROSIR',
            'name' => 'Daftar Harga Grosir',
            'customer_type' => CustomerType::GROSIR,
        ]);

        // Tier 1: 1 - 9 sak = Rp 64.000
        PricelistItem::create([
            'pricelist_id' => $grosirPricelist->id,
            'product_variant_id' => $variant->id,
            'min_qty' => 1,
            'unit_price' => 64_000.0,
        ]);

        // Tier 2: >= 10 sak = Rp 61.000
        PricelistItem::create([
            'pricelist_id' => $grosirPricelist->id,
            'product_variant_id' => $variant->id,
            'min_qty' => 10,
            'unit_price' => 61_000.0,
        ]);

        // Tier 3: >= 100 sak (Partai Besar) = Rp 58.000
        PricelistItem::create([
            'pricelist_id' => $grosirPricelist->id,
            'product_variant_id' => $variant->id,
            'min_qty' => 100,
            'unit_price' => 58_000.0,
        ]);

        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Toko Bangunan Maju Jaya',
            'customer_type' => CustomerType::GROSIR,
            'tax_id_type' => TaxIdType::NPWP_16,
            'tax_id_number' => '0123456789012345',
            'credit_limit' => 50_000_000.0,
        ]);

        // Skenario 1: Order 5 sak -> Cocok Tier 1 (Rp 64.000)
        $resolved1 = $this->resolver->resolve($variant, $customer, quantity: 5);
        $this->assertSame(64_000.0, $resolved1->unitPrice);
        $this->assertSame('customer_type_pricelist', $resolved1->source);

        // Skenario 2: Order 25 sak -> Cocok Tier 2 (Rp 61.000)
        $resolved2 = $this->resolver->resolve($variant, $customer, quantity: 25);
        $this->assertSame(61_000.0, $resolved2->unitPrice);

        // Skenario 3: Order 150 sak -> Cocok Tier 3 (Rp 58.000)
        $resolved3 = $this->resolver->resolve($variant, $customer, quantity: 150);
        $this->assertSame(58_000.0, $resolved3->unitPrice);
    }

    public function test_price_resolver_prioritizes_customer_assigned_pricelist(): void
    {
        $product = Product::create([
            'code' => 'PRD-002',
            'name' => 'Cat Tembok Putih 5kg',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'CAT-PUTIH-5KG',
            'name' => 'Cat Putih 5kg',
            'base_price' => 120_000.0,
        ]);

        // Default Pricelist Retail
        $retailPricelist = Pricelist::create([
            'code' => 'PL-RETAIL',
            'name' => 'Pricelist Retail Standard',
            'customer_type' => CustomerType::RETAIL,
        ]);
        PricelistItem::create([
            'pricelist_id' => $retailPricelist->id,
            'product_variant_id' => $variant->id,
            'min_qty' => 1,
            'unit_price' => 115_000.0,
        ]);

        // VIP Contract Pricelist
        $vipPricelist = Pricelist::create([
            'code' => 'PL-VIP',
            'name' => 'Pricelist Kontrak Khusus',
        ]);
        PricelistItem::create([
            'pricelist_id' => $vipPricelist->id,
            'product_variant_id' => $variant->id,
            'min_qty' => 1,
            'unit_price' => 100_000.0,
        ]);

        $customer = Customer::create([
            'code' => 'CUST-VIP',
            'name' => 'Hotel Tentrem',
            'customer_type' => CustomerType::RETAIL,
            'pricelist_id' => $vipPricelist->id, // Assigned khusus
        ]);

        $resolved = $this->resolver->resolve($variant, $customer, quantity: 1);
        $this->assertSame(100_000.0, $resolved->unitPrice);
        $this->assertSame('customer_assigned_pricelist', $resolved->source);
    }

    public function test_customer_credit_guard_validates_and_locks_overlimit(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-002',
            'name' => 'Kontraktor Bintang',
            'customer_type' => CustomerType::PROYEK,
            'credit_limit' => 10_000_000.0,
            'current_credit_used' => 8_000_000.0,
        ]);

        // Sisa plafon: 2.000.000
        $this->assertSame(2_000_000.0, $customer->remainingCreditLimit());

        // Order 1.500.000 masih dalam batas plafon -> Lolos
        $this->assertTrue($this->creditGuard->canProceed($customer, 1_500_000.0));

        // Order 2.500.000 melebihi plafon -> Harus gagal dan lempar CreditLimitExceededException
        $this->assertFalse($this->creditGuard->canProceed($customer, 2_500_000.0));

        $this->expectException(CreditLimitExceededException::class);
        $this->creditGuard->validate($customer, 2_500_000.0);
    }

    public function test_customer_credit_guard_consumes_and_releases_credit_atomically(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-003',
            'name' => 'Toko Subur',
            'customer_type' => CustomerType::GROSIR,
            'credit_limit' => 20_000_000.0,
            'current_credit_used' => 0.0,
        ]);

        // Menggunakan kredit 5.000.000
        $this->creditGuard->consumeCredit($customer, 5_000_000.0);
        $customer->refresh();
        $this->assertSame(5_000_000.0, (float) $customer->current_credit_used);
        $this->assertSame(15_000_000.0, $customer->remainingCreditLimit());

        // Pelunasan kredit 3.000.000 di modul Kas -> Plafon dipulihkan
        $this->creditGuard->releaseCredit($customer, 3_000_000.0);
        $customer->refresh();
        $this->assertSame(2_000_000.0, (float) $customer->current_credit_used);
        $this->assertSame(18_000_000.0, $customer->remainingCreditLimit());
    }

    public function test_customer_creation_api_enforces_coretax_16_digit_npwp(): void
    {
        $invalidResponse = $this->postJson(route('customers.store'), [
            'code' => 'CUST-ERR',
            'name' => 'Perusahaan A',
            'customer_type' => CustomerType::GROSIR->value,
            'tax_id_type' => TaxIdType::NPWP_16->value,
            'tax_id_number' => '123456789012345', // 15 digit (lama)
        ]);

        $invalidResponse->assertStatus(422);
        $invalidResponse->assertJsonValidationErrors(['tax_id_number']);

        // 2. Berhasil jika NPWP 16 digit resmi Coretax
        $validResponse = $this->postJson(route('customers.store'), [
            'code' => 'CUST-OK',
            'name' => 'PT Semesta Makmur',
            'customer_type' => CustomerType::GROSIR->value,
            'tax_id_type' => TaxIdType::NPWP_16->value,
            'tax_id_number' => '0123456789012345', // 16 digit
            'credit_limit' => 100_000_000.0,
        ]);

        $validResponse->assertStatus(201);
        $this->assertDatabaseHas('customers', [
            'code' => 'CUST-OK',
            'tax_id_number' => '0123456789012345',
        ]);
    }

    public function test_master_data_web_dashboard_renders_successfully(): void
    {
        $response = $this->get(route('master-data.index'));
        $response->assertStatus(200);
        $response->assertSee('MasterData Hub');
        $response->assertSee('EVEREST ERP 2.0');
    }

    public function test_simulate_pricing_api_calculates_and_checks_credit(): void
    {
        $product = Product::create([
            'code' => 'PRD-SIM',
            'name' => 'Produk Simulasi',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-SIM-01',
            'name' => 'Varian Simulasi',
            'base_price' => 50_000.0,
        ]);

        $customer = Customer::create([
            'code' => 'CUST-SIM',
            'name' => 'Konsumen Simulasi',
            'customer_type' => CustomerType::GROSIR,
            'credit_limit' => 5_000_000.0,
            'current_credit_used' => 1_000_000.0,
        ]);

        $response = $this->postJson(route('master-data.simulate-pricing'), [
            'variant_id' => $variant->id,
            'customer_id' => $customer->id,
            'quantity' => 10,
            'discounts' => '10+5%',
            'is_taxable' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'resolved_unit_price',
            'resolved_source',
            'calculation' => [
                'gross_amount',
                'discount_amount',
                'dpp',
                'ppn_rate',
                'ppn_amount',
                'net_amount',
            ],
            'credit_status' => [
                'can_proceed',
                'credit_limit',
                'current_used',
                'remaining',
                'after_transaction',
            ],
        ]);

        $this->assertTrue($response->json('credit_status.can_proceed'));
    }
}
