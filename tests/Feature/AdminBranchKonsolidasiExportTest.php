<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class AdminBranchKonsolidasiExportTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Branch::create([
            'code' => 'STR-MEDAN',
            'name' => 'Store Cabang Medan',
            'type' => BranchType::Store,
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $wh = Warehouse::create([
            'branch_id' => $this->store->id,
            'code' => 'WH-MEDAN',
            'name' => 'Gudang Medan',
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'PRD-SNACK',
            'name' => 'Keripik Singkong',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-SNACK-01',
            'name' => 'Keripik Singkong Pedas',
            'cost_price' => 5000,
            'base_price' => 10000,
            'is_active' => true,
        ]);

        StockInventory::create([
            'warehouse_id' => $wh->id,
            'product_variant_id' => $variant->id,
            'qty_on_hand' => 8,
            'safety_stock' => 5,
        ]);

        BranchProduct::create([
            'branch_id' => $this->store->id,
            'product_variant_id' => $variant->id,
            'bin_location' => 'Rak Makanan B-01',
            'min_stock' => 5,
            'max_stock' => 40,
            'reorder_point' => 15,
            'is_locally_active' => true,
        ]);

        CafeSale::create([
            'invoice_number' => 'INV-EXP-001',
            'branch_id' => $this->store->id,
            'customer_name' => 'Pelanggan Medan',
            'gross_amount' => 20000,
            'discount_amount' => 0,
            'net_amount' => 20000,
            'amount_paid' => 20000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => Carbon::today()->setTime(11, 0, 0),
        ]);
    }

    public function test_it_exports_konsolidasi_data_to_csv_file(): void
    {
        $response = $this->get(route('admin.cabang.konsolidasi', [
            'date' => Carbon::today()->toDateString(),
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="laporan_konsolidasi_cabang_', (string) $response->headers->get('Content-Disposition'));

        // Ambil isi streaming output CSV
        $content = $response->streamedContent();

        // Verifikasi konten CSV
        $this->assertStringContainsString('LAPORAN KONSOLIDASI & MONITORING MULTI-CABANG', $content);
        $this->assertStringContainsString('Total Valuasi Persediaan Jaringan:', $content);
        $this->assertStringContainsString('MATRIKS KOMPARASI KINERJA PENJUALAN', $content);
        $this->assertStringContainsString('Store Cabang Medan', $content);
        $this->assertStringContainsString('STR-MEDAN', $content);
        $this->assertStringContainsString('RADAR PERINGATAN KRITIS REORDER POINT', $content);
        $this->assertStringContainsString('SKU-SNACK-01', $content);
        $this->assertStringContainsString('Rak Makanan B-01', $content);
    }
}
