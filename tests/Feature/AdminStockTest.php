<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class AdminStockTest extends TestCase
{
    use RefreshDatabase;

    private Warehouse $warehouse;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'BR-PST',
            'is_headquarters' => true,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'branch_id' => $branch->id,
            'name' => 'Gudang Utama Pusat',
            'code' => 'GDG-PST',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Storage Drive',
            'code' => 'CAT-STR',
            'slug' => 'storage-drive',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'SSD NVMe 1TB',
            'code' => 'SSD-1TB',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SSD-1TB-GEN4',
            'name' => 'PCIe 4.0',
            'base_price' => 1200000,
            'cost_price' => 950000,
            'is_active' => true,
        ]);
    }

    public function test_admin_stock_page_can_be_rendered(): void
    {
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 15,
            'safety_stock' => 5,
        ]);

        $response = $this->get(route('admin.stok-limit'));

        $response->assertStatus(200);
        $response->assertSee('DAFTAR STOK LIMIT');
        $response->assertSee('SSD-1TB-GEN4');
        $response->assertSee('Gudang Utama Pusat');
    }

    public function test_admin_can_fetch_stocks_via_json(): void
    {
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 2,
            'safety_stock' => 10,
        ]);

        $response = $this->getJson(route('admin.stok-limit'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment(['qty_on_hand' => 2]);
    }

    public function test_admin_can_update_stock_limits_and_qty(): void
    {
        $stock = StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 20,
            'safety_stock' => 5,
        ]);

        $response = $this->putJson(route('admin.stok-limit.update', $stock), [
            'safety_stock' => 8,
            'qty_on_hand' => 25,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('stock_inventories', [
            'id' => $stock->id,
            'safety_stock' => 8,
            'qty_on_hand' => 25,
        ]);
    }
}
