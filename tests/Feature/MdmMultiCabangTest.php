<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductPricing;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class MdmMultiCabangTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $hq;

    protected Branch $dc;

    protected Branch $store;

    protected Product $product;

    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hq = Branch::create([
            'code' => 'HQ-PST',
            'name' => 'Maya Graha Kencana Kantor Pusat',
            'type' => BranchType::Hq,
            'is_headquarters' => true,
            'is_active' => true,
        ]);

        $this->dc = Branch::create([
            'code' => 'DC-JKT01',
            'name' => 'Distribution Center Pusat Jakarta',
            'type' => BranchType::Dc,
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $this->store = Branch::create([
            'code' => 'STR-THM',
            'name' => 'Kopi Bagus Thamrin (Store)',
            'type' => BranchType::Store,
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'code' => 'PRD-001',
            'name' => 'Kopi Arabika Gayo',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'SKU-KOP-GAYO-250',
            'barcode' => '8991234567890',
            'name' => 'Kopi Arabika Gayo 250gr',
            'base_price' => 75000,
            'cost_price' => 45000,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_branch_mdm_list(): void
    {
        $response = $this->get(route('admin.cabang'));

        $response->assertStatus(200);
        $response->assertSee('Jaringan Cabang', false);
        $response->assertSee('DC-JKT01');
        $response->assertSee('STR-THM');
        $response->assertSee('HQ - Kantor Pusat');
    }

    public function test_admin_can_create_new_distribution_center_branch(): void
    {
        $payload = [
            'code' => 'DC-SBY01',
            'name' => 'Distribution Center Surabaya Logistik',
            'type' => BranchType::Dc->value,
            'address' => 'Kawasan Industri Rungkut, Surabaya',
            'phone' => '031-1234567',
            'radius_meters' => 150,
        ];

        $response = $this->post(route('admin.cabang.store'), $payload);

        $response->assertRedirect(route('admin.cabang'));
        $this->assertDatabaseHas('branches', [
            'code' => 'DC-SBY01',
            'type' => 'DC',
        ]);
    }

    public function test_admin_can_localize_product_bin_and_rop(): void
    {
        $branchProduct = BranchProduct::create([
            'branch_id' => $this->store->id,
            'product_variant_id' => $this->variant->id,
            'bin_location' => 'Rak-A01',
            'min_stock' => 5,
            'max_stock' => 50,
            'reorder_point' => 15,
            'is_locally_active' => true,
        ]);

        $response = $this->put(route('admin.cabang.produk.update', $branchProduct->id), [
            'bin_location' => 'Lorong-B-Rak-02',
            'min_stock' => 10,
            'max_stock' => 60,
            'reorder_point' => 20,
            'is_locally_active' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('branch_products', [
            'id' => $branchProduct->id,
            'bin_location' => 'Lorong-B-Rak-02',
            'reorder_point' => 20,
        ]);
    }

    public function test_rop_alert_logic_triggers_when_stock_at_or_below_reorder_point(): void
    {
        $bp = BranchProduct::create([
            'branch_id' => $this->store->id,
            'product_variant_id' => $this->variant->id,
            'reorder_point' => 15,
            'is_locally_active' => true,
        ]);

        // Stok 20: Aman (di atas ROP 15)
        $this->assertFalse($bp->needsReorder(20.0));

        // Stok 15: Alert pemicu transfer dari DC
        $this->assertTrue($bp->needsReorder(15.0));

        // Stok 5: Alert kritis
        $this->assertTrue($bp->needsReorder(5.0));
    }

    public function test_admin_can_sync_national_products_to_branch(): void
    {
        $this->assertDatabaseMissing('branch_products', [
            'branch_id' => $this->store->id,
            'product_variant_id' => $this->variant->id,
        ]);

        $response = $this->post(route('admin.cabang.sync-produk', $this->store->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('branch_products', [
            'branch_id' => $this->store->id,
            'product_variant_id' => $this->variant->id,
            'reorder_point' => 10,
        ]);
    }

    public function test_admin_can_define_zone_pricing_and_check_effectivity(): void
    {
        $pricing = ProductPricing::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->store->id,
            'zone_code' => 'ZONE-JABODETABEK',
            'base_cost' => 48000,
            'sale_price' => 80000,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
        ]);

        $this->assertTrue($pricing->isEffectiveOn(Carbon::today()));
        $this->assertTrue($pricing->isEffectiveOn(Carbon::today()->addDays(10)));
        $this->assertFalse($pricing->isEffectiveOn(Carbon::today()->addDays(35)));

        $response = $this->get(route('admin.cabang.zonasi-harga'));
        $response->assertStatus(200);
        $response->assertSee('ZONE-JABODETABEK');
        $response->assertSee('80.000');
    }

    public function test_stock_inventory_calculates_qty_available_correctly(): void
    {
        $warehouse = Warehouse::create([
            'branch_id' => $this->store->id,
            'code' => 'WH-THM01',
            'name' => 'Gudang Kasir Toko Thamrin',
            'is_active' => true,
        ]);

        $inventory = StockInventory::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 100,
            'qty_reserved' => 25,
            'safety_stock' => 10,
        ]);

        $this->assertEquals(75, $inventory->qty_available);
    }
}
