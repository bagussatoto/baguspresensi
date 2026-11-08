<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Employee\Models\Shift;
use App\Models\ShiftStockRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\StockTransferDispatch;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class StoreGoodsReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $dc;

    protected Branch $store;

    protected Warehouse $dcWarehouse;

    protected Warehouse $storeWarehouse;

    protected ProductVariant $variant;

    protected Employee $cashier;

    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shift = Shift::firstOrCreate(
            ['code' => 'SHIFT-1'],
            ['name' => 'Shift 1 Pagi', 'start_time' => '07:00:00', 'end_time' => '15:00:00', 'is_active' => true]
        );

        // 1. Setup Distribution Center (DC)
        $this->dc = Branch::create([
            'code' => 'DC-CENTRAL',
            'name' => 'Pusat Distribusi Logistik',
            'type' => BranchType::Dc,
            'is_headquarters' => false,
            'is_active' => true,
        ]);
        $this->dcWarehouse = Warehouse::create([
            'branch_id' => $this->dc->id,
            'code' => 'WH-DC',
            'name' => 'Gudang DC Pusat',
            'is_active' => true,
        ]);

        // 2. Setup Toko Cabang (Store)
        $this->store = Branch::create([
            'code' => 'STR-BANDUNG',
            'name' => 'Store Cabang Bandung',
            'type' => BranchType::Store,
            'is_headquarters' => false,
            'is_active' => true,
        ]);
        $this->storeWarehouse = Warehouse::create([
            'branch_id' => $this->store->id,
            'code' => 'WH-STR-BDG',
            'name' => 'Gudang Toko Bandung',
            'is_active' => true,
        ]);

        // 3. Setup Karyawan Kasir Toko
        $this->cashier = Employee::create([
            'branch_id' => $this->store->id,
            'code' => 'EMP-KSR-BDG',
            'name' => 'Ahmad Kasir Bandung',
            'role' => 'kasir',
            'is_active' => true,
        ]);

        // 4. Setup Produk & Varian
        $product = Product::create([
            'code' => 'PRD-MINUMAN',
            'name' => 'Teh Botol Sosro',
            'is_active' => true,
        ]);
        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-TEH-BOTOL',
            'name' => 'Teh Botol Sosro 450ml',
            'cost_price' => 4000,
            'base_price' => 6000,
            'is_active' => true,
        ]);

        // 5. Lokalisasi Rak di Toko
        BranchProduct::create([
            'branch_id' => $this->store->id,
            'product_variant_id' => $this->variant->id,
            'bin_location' => 'Showcase Minuman Rak B-02',
            'min_stock' => 5,
            'max_stock' => 50,
            'reorder_point' => 15,
            'is_locally_active' => true,
        ]);

        // 6. Stok Awal di Toko = 5 unit
        StockInventory::create([
            'warehouse_id' => $this->storeWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 5,
            'safety_stock' => 5,
        ]);
    }

    public function test_cashier_can_confirm_goods_receipt_from_dc_and_increment_store_stock(): void
    {
        // DC mengirim transfer supply 30 unit (Status: in_transit)
        $transfer = StockTransferDispatch::create([
            'transfer_code' => 'TRF-DC-BDG-001',
            'from_warehouse_id' => $this->dcWarehouse->id,
            'to_warehouse_id' => $this->storeWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty' => 30,
            'status' => 'in_transit',
            'dispatched_at' => Carbon::now()->subHours(2),
            'operator_notes' => 'Pengiriman pasokan rutin dari DC',
        ]);

        // Catat juga STO yang dibuat toko sebelumnya
        $sto = ShiftStockRequest::create([
            'request_number' => 'STO-BDG-001',
            'branch_id' => $this->store->id,
            'employee_id' => $this->cashier->id,
            'item_category' => 'Minuman',
            'item_name' => 'Teh Botol Sosro 450ml',
            'qty_requested' => 30,
            'status' => 'pending',
        ]);

        // Kasir mengonfirmasi penerimaan fisik barang
        $response = $this->withSession([
            'presensi2_employee_id' => $this->cashier->id,
            'presensi2_branch_id' => $this->store->id,
        ])->postJson(route('presensi2.transfer.terima', $transfer->id), [
            'qty_received' => 30,
            'notes' => 'Fisik lengkap 30 botol, langsung masuk Showcase Rak B-02',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'new_stock_qty' => 35, // 5 stok awal + 30 kiriman DC = 35
        ]);

        // Verifikasi database: StockTransferDispatch status becomes received
        $transfer->refresh();
        $this->assertEquals('received', $transfer->status);
        $this->assertNotNull($transfer->received_at);
        $this->assertStringContainsString('Diterima oleh Ahmad Kasir Bandung', (string) $transfer->operator_notes);

        // Verifikasi database: StockInventory toko bertambah
        $stock = StockInventory::where('warehouse_id', $this->storeWarehouse->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(35, $stock->qty_on_hand);

        // Verifikasi database: STO terkait menjadi fulfilled
        $sto->refresh();
        $this->assertEquals('fulfilled', $sto->status);
        $this->assertNotNull($sto->fulfilled_at);
    }

    public function test_cashier_can_confirm_goods_receipt_with_discrepancy(): void
    {
        // DC mengirim 25 unit, tapi toko hanya menerima 23 (2 botol pecah di perjalanan)
        $transfer = StockTransferDispatch::create([
            'transfer_code' => 'TRF-DC-BDG-002',
            'from_warehouse_id' => $this->dcWarehouse->id,
            'to_warehouse_id' => $this->storeWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty' => 25,
            'status' => 'in_transit',
            'dispatched_at' => Carbon::now()->subHour(),
        ]);

        $response = $this->withSession([
            'presensi2_employee_id' => $this->cashier->id,
            'presensi2_branch_id' => $this->store->id,
        ])->post(route('presensi2.transfer.terima', $transfer->id), [
            'qty_received' => 23,
            'notes' => '2 botol pecah saat kurir menurunkan barang',
        ]);

        $response->assertSessionHas('success');

        // Status transfer menjadi 'selisih'
        $transfer->refresh();
        $this->assertEquals('selisih', $transfer->status);

        // Stok toko bertambah 23 unit (5 + 23 = 28)
        $stock = StockInventory::where('warehouse_id', $this->storeWarehouse->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(28, $stock->qty_on_hand);
    }

    public function test_cannot_receive_already_received_transfer(): void
    {
        $transfer = StockTransferDispatch::create([
            'transfer_code' => 'TRF-DC-BDG-003',
            'from_warehouse_id' => $this->dcWarehouse->id,
            'to_warehouse_id' => $this->storeWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty' => 10,
            'status' => 'received',
            'received_at' => Carbon::now()->subMinutes(10),
        ]);

        $response = $this->withSession([
            'presensi2_employee_id' => $this->cashier->id,
            'presensi2_branch_id' => $this->store->id,
        ])->postJson(route('presensi2.transfer.terima', $transfer->id), [
            'qty_received' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Surat jalan ini sudah pernah diterima sebelumnya.',
        ]);
    }
}
