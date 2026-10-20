<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Pricelist;
use Modules\MasterData\Domain\Models\PricelistItem;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Tests\TestCase;

class AdminPricelistTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name' => 'Kabel & Aksesoris',
            'code' => 'CAT-KBL',
            'slug' => 'kabel-aksesoris',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Kabel LAN Cat6 5M',
            'code' => 'CAB-LAN6',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'CAB-LAN6-5M',
            'name' => '5 Meter',
            'base_price' => 35000,
            'cost_price' => 20000,
            'is_active' => true,
        ]);
    }

    public function test_admin_pricelist_page_can_be_rendered(): void
    {
        $pricelist = Pricelist::create([
            'name' => 'Harga Retail Warnet Standar',
            'code' => 'PL-RET',
            'customer_type' => 'retail',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        PricelistItem::create([
            'pricelist_id' => $pricelist->id,
            'product_variant_id' => $this->variant->id,
            'unit_price' => 35000,
            'min_qty' => 1,
            'discount_percent' => 0,
        ]);

        $response = $this->get(route('admin.harga'));

        $response->assertStatus(200);
        $response->assertSee('DAFTAR HARGA');
        $response->assertSee('Harga Retail Warnet Standar');
        $response->assertSee('PL-RET');
    }

    public function test_admin_can_store_new_pricelist(): void
    {
        $response = $this->postJson(route('admin.harga.store'), [
            'name' => 'Daftar Harga Reseller VIP',
            'code' => 'PL-RSL',
            'customer_type' => 'reseller',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('pricelists', [
            'name' => 'Daftar Harga Reseller VIP',
            'code' => 'PL-RSL',
            'customer_type' => 'reseller',
        ]);
    }

    public function test_admin_can_update_pricelist(): void
    {
        $pricelist = Pricelist::create([
            'name' => 'Pricelist Lama',
            'code' => 'PL-OLD',
            'customer_type' => 'retail',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response = $this->putJson(route('admin.harga.update', $pricelist), [
            'name' => 'Pricelist Grosir Nasional',
            'customer_type' => 'grosir',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('pricelists', [
            'id' => $pricelist->id,
            'name' => 'Pricelist Grosir Nasional',
            'customer_type' => 'grosir',
        ]);
    }

    public function test_admin_can_store_or_update_pricelist_item(): void
    {
        $pricelist = Pricelist::create([
            'name' => 'Pricelist Tiering',
            'code' => 'PL-TIER',
            'customer_type' => 'proyek',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.harga.item', $pricelist), [
            'product_variant_id' => $this->variant->id,
            'unit_price' => 30000,
            'min_qty' => 10,
            'discount_percent' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('pricelist_items', [
            'pricelist_id' => $pricelist->id,
            'product_variant_id' => $this->variant->id,
            'unit_price' => 30000,
            'min_qty' => 10,
            'discount_percent' => 5,
        ]);
    }

    public function test_admin_can_delete_pricelist(): void
    {
        $pricelist = Pricelist::create([
            'name' => 'Pricelist Dihapus',
            'code' => 'PL-DEL',
            'customer_type' => 'retail',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response = $this->deleteJson(route('admin.harga.destroy', $pricelist));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('pricelists', [
            'id' => $pricelist->id,
        ]);
    }
}
