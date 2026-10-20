<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Brand;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Peripheral Gaming',
            'code' => 'CAT-PER',
            'slug' => 'peripheral-gaming',
            'is_active' => true,
        ]);

        $this->brand = Brand::create([
            'name' => 'Logitech G',
            'code' => 'LOGI-G',
            'slug' => 'logitech-g',
            'is_active' => true,
        ]);
    }

    public function test_admin_product_page_can_be_rendered(): void
    {
        $product = Product::create([
            'name' => 'Mouse Gaming G102',
            'code' => 'PRD-M102',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PRD-M102-REG',
            'name' => 'Black Edition',
            'base_price' => 250000,
            'cost_price' => 190000,
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.produk'));

        $response->assertStatus(200);
        $response->assertSee('DAFTAR KATALOG PRODUK');
        $response->assertSee('Mouse Gaming G102');
        $response->assertSee('Logitech G');
    }

    public function test_admin_can_fetch_products_via_json(): void
    {
        Product::create([
            'name' => 'Keyboard Mechanical K55',
            'code' => 'PRD-K55',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('admin.produk'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment(['name' => 'Keyboard Mechanical K55']);
    }

    public function test_admin_can_store_new_product_with_variant(): void
    {
        $response = $this->postJson(route('admin.produk.store'), [
            'name' => 'Headset Gaming Artemis 7.1',
            'code' => 'PRD-ART7',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'base_price' => 650000,
            'cost_price' => 480000,
            'description' => 'Surround sound gaming headset',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Headset Gaming Artemis 7.1',
            'code' => 'PRD-ART7',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => 'PRD-ART7-REG',
            'base_price' => 650000,
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'name' => 'Original Mouse',
            'code' => 'PRD-OLD',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PRD-OLD-REG',
            'name' => 'Standar',
            'base_price' => 100000,
            'cost_price' => 70000,
            'is_active' => true,
        ]);

        $response = $this->putJson(route('admin.produk.update', $product), [
            'name' => 'Updated Mouse Pro',
            'base_price' => 150000,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Mouse Pro',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'base_price' => 150000,
        ]);
    }

    public function test_admin_can_toggle_product_status(): void
    {
        $product = Product::create([
            'name' => 'Toggle Item',
            'code' => 'PRD-TOG',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.produk.toggle-status', $product));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_active' => false,
        ]);

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::create([
            'name' => 'Obsolete Product',
            'code' => 'PRD-DEL',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->deleteJson(route('admin.produk.destroy', $product));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
