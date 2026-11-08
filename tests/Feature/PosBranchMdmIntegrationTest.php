<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductPricing;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class PosBranchMdmIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Warehouse $warehouse;

    private Employee $employee;

    private Shift $shift;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'BR-THM',
            'name' => 'Kopi Bagus Thamrin',
            'type' => BranchType::Store,
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'WH-THM01',
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

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
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

        // Setup clock-in hari ini agar kasir bisa bertransaksi
        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in_at' => Carbon::now()->subHour(),
        ]);
    }

    public function test_pos_cashier_shows_bin_location_and_real_available_stock(): void
    {
        // 1. Lokalisasi Rak & ROP Cabang
        BranchProduct::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'bin_location' => 'Rak-A-03',
            'min_stock' => 5,
            'max_stock' => 50,
            'reorder_point' => 10,
            'is_locally_active' => true,
        ]);

        // 2. Stok: On-hand 50, Reserved 15 -> Available 35
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 50,
            'qty_reserved' => 15,
            'safety_stock' => 5,
        ]);

        $response = $this->withSession([
            'presensi2_employee_id' => $this->employee->id,
            'presensi2_branch_id' => $this->branch->id,
        ])->get(route('pos'));

        $response->assertStatus(200);
        $response->assertSee('Rak-A-03');
        $response->assertSee('35'); // qty_available
    }

    public function test_pos_cashier_uses_branch_zone_pricing_for_transaction(): void
    {
        // Harga dasar nasional: 20.000
        // Ditetapkan harga zonasi khusus cabang Thamrin: 25.000
        ProductPricing::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->branch->id,
            'zone_code' => 'ZONE-BR-THM',
            'base_cost' => 12000,
            'sale_price' => 25000,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(20),
        ]);

        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 20,
            'qty_reserved' => 0,
            'safety_stock' => 2,
        ]);

        // Transaksi kasir beli 2 unit
        $payload = [
            'transaction_type' => 'fnb',
            'payment_method' => 'cash',
            'items' => [
                [
                    'variant_id' => $this->variant->id,
                    'qty' => 2,
                ],
            ],
        ];

        $response = $this->withSession([
            'presensi2_employee_id' => $this->employee->id,
            'presensi2_branch_id' => $this->branch->id,
        ])->post(route('presensi2.penjualan.transaksi'), $payload);

        $response->assertRedirect(route('presensi2.penjualan'));

        // Tagihan 2 x 25.000 = 50.000 (bukan 2 x 20.000 = 40.000)
        $sale = CafeSale::where('branch_id', $this->branch->id)->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(50000, (float) $sale->total_fnb);
        $this->assertEquals(50000, (float) $sale->net_amount);
    }

    public function test_pos_transaction_decrements_stock_and_triggers_rop_monitoring(): void
    {
        BranchProduct::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'bin_location' => 'Rak-A-01',
            'reorder_point' => 10,
            'is_locally_active' => true,
        ]);

        $inv = StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 12,
            'qty_reserved' => 0,
            'safety_stock' => 2,
        ]);

        // Jual 4 unit -> sisa on_hand 8 (dibawah ROP 10)
        $payload = [
            'transaction_type' => 'fnb',
            'payment_method' => 'cash',
            'items' => [
                [
                    'variant_id' => $this->variant->id,
                    'qty' => 4,
                ],
            ],
        ];

        $this->withSession([
            'presensi2_employee_id' => $this->employee->id,
            'presensi2_branch_id' => $this->branch->id,
        ])->post(route('presensi2.penjualan.transaksi'), $payload);

        $inv->refresh();
        $this->assertEquals(8, $inv->qty_on_hand);
        $this->assertEquals(8, $inv->qty_available);

        $bp = BranchProduct::where('branch_id', $this->branch->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();

        $this->assertTrue($bp->needsReorder((float) $inv->qty_available));
    }
}
