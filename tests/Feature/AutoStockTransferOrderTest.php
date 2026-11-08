<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use App\Models\ShiftStockRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Modules\MasterData\Domain\Services\AutoStockTransferOrderService;
use Tests\TestCase;

class AutoStockTransferOrderTest extends TestCase
{
    use RefreshDatabase;

    private Branch $dc;

    private Branch $store;

    private Warehouse $dcWarehouse;

    private Warehouse $storeWarehouse;

    private Employee $cashier;

    private Shift $shift;

    private ProductVariant $variant;

    private BranchProduct $branchProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Distribution Center (DC)
        $this->dc = Branch::create([
            'code' => 'DC-JKT01',
            'name' => 'Distribution Center Pusat Jakarta',
            'type' => BranchType::Dc,
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $this->dcWarehouse = Warehouse::create([
            'branch_id' => $this->dc->id,
            'code' => 'WH-DC01',
            'name' => 'Gudang Pusat Distribusi DC',
            'type' => 'storage',
            'is_active' => true,
        ]);

        // 2. Cabang Toko Ritel (Store)
        $this->store = Branch::create([
            'code' => 'STR-THM',
            'name' => 'Kopi Bagus Thamrin (Store)',
            'type' => BranchType::Store,
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $this->storeWarehouse = Warehouse::create([
            'branch_id' => $this->store->id,
            'code' => 'WH-STR01',
            'name' => 'Gudang Kasir Toko Thamrin',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'code' => 'SHIFT-1',
            'name' => 'Shift 1 Pagi',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'order' => 1,
            'is_active' => true,
        ]);

        $this->cashier = Employee::create([
            'branch_id' => $this->store->id,
            'code' => 'EMP-001',
            'name' => 'Bagus Kasir',
            'pin' => Hash::make('123456'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $cat = Category::create([
            'code' => 'CAT-FNB',
            'name' => 'Food and Beverage',
            'slug' => 'fnb',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $cat->id,
            'code' => 'PRD-KOP-01',
            'name' => 'Kopi Susu Gula Aren',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-KSGA-01',
            'name' => 'Kopi Susu Gula Aren Regular',
            'base_price' => 20000,
            'cost_price' => 10000,
            'is_active' => true,
        ]);

        $this->branchProduct = BranchProduct::create([
            'branch_id' => $this->store->id,
            'product_variant_id' => $this->variant->id,
            'bin_location' => 'Rak-A-01',
            'min_stock' => 5,
            'max_stock' => 50,
            'reorder_point' => 15,
            'is_locally_active' => true,
        ]);

        // Clock-in kasir hari ini
        AttendanceRecord::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->store->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in_at' => Carbon::now()->subHour(),
        ]);
    }

    public function test_service_creates_auto_draft_sto_when_stock_hits_reorder_point(): void
    {
        $service = app(AutoStockTransferOrderService::class);

        // Stok sisa 10 (ROP 15, Max 50 -> Butuh pasok 50 - 10 = 40 unit)
        $sto = $service->evaluateAndCreateDraftSto(
            $this->variant,
            $this->store->id,
            10.0,
            $this->cashier->id,
            $this->shift->id
        );

        $this->assertNotNull($sto);
        $this->assertInstanceOf(ShiftStockRequest::class, $sto);
        $this->assertEquals(40, $sto->qty_requested);
        $this->assertEquals('pending', $sto->status);
        $this->assertEquals('urgent', $sto->urgency);
        $this->assertStringContainsString('[AUTO-ROP MDM]', (string) $sto->notes);
        $this->assertStringContainsString('Distribution Center', (string) $sto->notes);
    }

    public function test_service_prevents_duplicate_pending_draft_sto(): void
    {
        $service = app(AutoStockTransferOrderService::class);

        $sto1 = $service->evaluateAndCreateDraftSto(
            $this->variant,
            $this->store->id,
            8.0,
            $this->cashier->id,
            $this->shift->id
        );

        $sto2 = $service->evaluateAndCreateDraftSto(
            $this->variant,
            $this->store->id,
            5.0,
            $this->cashier->id,
            $this->shift->id
        );

        $this->assertEquals($sto1->id, $sto2->id);
        $this->assertEquals(1, ShiftStockRequest::where('branch_id', $this->store->id)->count());
    }

    public function test_pos_sale_triggers_auto_draft_sto_on_rop_breach(): void
    {
        // Stok toko awalnya 18 unit (di atas ROP 15)
        StockInventory::create([
            'warehouse_id' => $this->storeWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 18,
            'qty_reserved' => 0,
            'safety_stock' => 2,
        ]);

        $this->assertEquals(0, ShiftStockRequest::count());

        // Kasir jual 5 unit -> Sisa stok menjadi 13 unit (dibawah ROP 15)
        $payload = [
            'transaction_type' => 'fnb',
            'payment_method' => 'cash',
            'items' => [
                [
                    'variant_id' => $this->variant->id,
                    'qty' => 5,
                ],
            ],
        ];

        $response = $this->withSession([
            'presensi2_employee_id' => $this->cashier->id,
            'presensi2_branch_id' => $this->store->id,
        ])->post(route('presensi2.penjualan.transaksi'), $payload);

        $response->assertRedirect(route('presensi2.penjualan'));

        // Sistem otomatis menerbitkan 1 tiket ShiftStockRequest STO
        $this->assertEquals(1, ShiftStockRequest::count());
        $sto = ShiftStockRequest::first();
        $this->assertStringContainsString('[AUTO-ROP MDM]', (string) $sto->notes);
        $this->assertEquals('pending', $sto->status);
    }

    public function test_admin_can_fulfill_auto_draft_sto_transferring_stock_from_dc_to_store(): void
    {
        // Stok DC: 200 unit
        $dcStock = StockInventory::create([
            'warehouse_id' => $this->dcWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 200,
            'qty_reserved' => 0,
            'safety_stock' => 20,
        ]);

        // Stok Toko Cabang: 8 unit
        $storeStock = StockInventory::create([
            'warehouse_id' => $this->storeWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 8,
            'qty_reserved' => 0,
            'safety_stock' => 5,
        ]);

        // Buat tiket auto-draft STO untuk 30 unit
        $sto = ShiftStockRequest::create([
            'request_number' => 'STO-ROP-TEST-001',
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->store->id,
            'shift_id' => $this->shift->id,
            'item_category' => 'F&B',
            'item_name' => $this->variant->name,
            'qty_requested' => 30,
            'unit' => 'pcs',
            'urgency' => 'urgent',
            'notes' => '[AUTO-ROP MDM] Pasokan dari DC',
            'status' => 'pending',
        ]);

        // Admin DC klik "Penuhi & Kirim dari DC"
        $response = $this->post(route('admin.distribusi.penuhi', $sto->id), [
            'qty_fulfilled' => 30,
        ]);

        $response->assertRedirect(route('admin.distribusi'));
        $sto->refresh();
        $this->assertEquals('fulfilled', $sto->status);

        // Verifikasi mutasi stok fisik di kedua sisi:
        // Stok DC berkurang 30 (200 -> 170)
        $dcStock->refresh();
        $this->assertEquals(170, $dcStock->qty_on_hand);

        // Stok Toko bertambah 30 (8 -> 38)
        $storeStock->refresh();
        $this->assertEquals(38, $storeStock->qty_on_hand);
    }
}
