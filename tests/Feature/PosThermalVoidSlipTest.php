<?php

namespace Tests\Feature;

use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafePayment;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class PosThermalVoidSlipTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $cashier;

    private Employee $supervisor;

    private Shift $shift;

    private Warehouse $warehouse;

    private ProductVariant $variantDrink;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::firstOrCreate(
            ['id' => 3],
            [
                'name' => 'MGK Cyber Outlet',
                'code' => 'MGK-01',
                'address' => 'Jl. Cyber Raya No. 10 Jakarta Pusat',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
                'is_active' => true,
            ]
        );

        $this->cashier = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bagus Kasir',
            'code' => 'EMP-KSR-01',
            'position' => 'Kasir POS',
            'role' => EmployeeRole::KASIR,
            'password' => Hash::make('password123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        $this->supervisor = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Pak Joko Supervisor',
            'code' => 'EMP-SPV-01',
            'position' => 'Supervisor Toko',
            'role' => EmployeeRole::SUPERVISOR,
            'password' => Hash::make('superpassword'),
            'pin' => Hash::make('998877'),
            'is_active' => true,
        ]);

        $this->shift = Shift::firstOrCreate(
            ['name' => 'Shift Pagi'],
            [
                'code' => 'S1',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'is_night_shift' => false,
            ]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['id' => 5],
            [
                'name' => 'Gudang Utama POS MGK',
                'code' => 'GDG-POS-01',
                'branch_id' => $this->branch->id,
                'is_active' => true,
            ]
        );

        $uom = UnitOfMeasurement::firstOrCreate(
            ['code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_active' => true]
        );

        $category = Category::firstOrCreate(
            ['code' => 'CAT-DRINK'],
            ['name' => 'Minuman', 'slug' => 'minuman', 'is_active' => true]
        );

        $product = Product::firstOrCreate(
            ['code' => 'PRD-AQUA-600'],
            [
                'name' => 'Aqua 600ml',
                'category_id' => $category->id,
                'is_active' => true,
            ]
        );

        $this->variantDrink = ProductVariant::firstOrCreate(
            ['product_id' => $product->id, 'name' => 'Aqua Botol Dingin'],
            [
                'sku' => 'SKU-AQUA-COLD',
                'uom_id' => $uom->id,
                'base_price' => 5000.0,
                'cost_price' => 3000.0,
                'is_active' => true,
            ]
        );

        StockInventory::updateOrCreate(
            [
                'product_variant_id' => $this->variantDrink->id,
                'warehouse_id' => $this->warehouse->id,
            ],
            [
                'qty_on_hand' => 100,
                'qty_reserved' => 0,
            ]
        );
    }

    /**
     * Test 1: Kasir membatalkan nota POS dengan PIN Supervisor, mendapatkan respon JSON berisi `void_data` lengkap.
     */
    public function test_cashier_can_void_sale_with_supervisor_pin_and_receive_void_data(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-VOID-TEST-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Budi Gamer',
            'total_fnb' => 15000.0,
            'gross_amount' => 15000.0,
            'net_amount' => 15000.0,
            'amount_paid' => 20000.0,
            'change_amount' => 5000.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        CafeSaleItem::create([
            'cafe_sale_id' => $sale->id,
            'product_variant_id' => $this->variantDrink->id,
            'item_type' => 'fnb',
            'item_name' => $this->variantDrink->name,
            'qty' => 3,
            'unit_price' => 5000.0,
            'subtotal' => 15000.0,
        ]);

        CafePayment::create([
            'cafe_sale_id' => $sale->id,
            'payment_number' => 'PAY-VOID-001',
            'payment_method' => 'cash',
            'amount_due' => 15000.0,
            'amount_paid' => 20000.0,
            'status' => 'completed',
        ]);

        // Eksekusi pembatalan via route presensi2.penjualan.batal
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.batal', ['sale' => $sale->id]), [
                'reason' => 'Salah klik varian minuman oleh kasir',
                'supervisor_pin' => '998877',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'payment_status' => 'cancelled',
            'invoice_number' => 'INV-VOID-TEST-001',
        ]);

        $json = $response->json();
        $this->assertArrayHasKey('void_data', $json);
        $this->assertEquals('INV-VOID-TEST-001', $json['void_data']['invoice_number']);
        $this->assertEquals('Salah klik varian minuman oleh kasir', $json['void_data']['reason']);
        $this->assertEquals('Pak Joko Supervisor', $json['void_data']['supervisor_name']);
        $this->assertEquals('Bagus Kasir', $json['void_data']['cashier_name']);
        $this->assertCount(1, $json['void_data']['items']);
        $this->assertEquals(15000.0, $json['void_data']['net_amount']);

        // Verifikasi database
        $freshSale = $sale->fresh();
        $this->assertEquals('cancelled', $freshSale->payment_status);
        $this->assertEquals($this->supervisor->id, $freshSale->void_by);
        $this->assertNotNull($freshSale->void_at);
    }

    /**
     * Test 2: Route presensi2.penjualan.void-slip menampilkan slip struk thermal siap cetak.
     */
    public function test_void_slip_route_renders_thermal_view(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-VOID-TEST-002',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Andi Pelanggan',
            'total_fnb' => 10000.0,
            'gross_amount' => 10000.0,
            'net_amount' => 10000.0,
            'payment_method' => 'cash',
            'payment_status' => 'cancelled',
            'void_by' => $this->supervisor->id,
            'void_reason' => 'Pelanggan membatalkan pesanan',
            'void_at' => Carbon::now(),
        ]);

        CafeSaleItem::create([
            'cafe_sale_id' => $sale->id,
            'product_variant_id' => $this->variantDrink->id,
            'item_type' => 'fnb',
            'item_name' => $this->variantDrink->name,
            'qty' => 2,
            'unit_price' => 5000.0,
            'subtotal' => 10000.0,
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.penjualan.void-slip', ['sale' => $sale->id, 'autoprint' => 1]));

        $response->assertStatus(200);
        $response->assertSee('STRUK PEMBATALAN (VOID)');
        $response->assertSee('BUKTI SAH RETUR FISIK KASIR');
        $response->assertSee('INV-VOID-TEST-002');
        $response->assertSee('Bagus Kasir');
        $response->assertSee('Pak Joko Supervisor');
        $response->assertSee('Pelanggan membatalkan pesanan');
        $response->assertSee('Aqua Botol Dingin');
        $response->assertSee('BATAL / REFUND KAS');
    }

    /**
     * Test 3: Route presensi2.penjualan.void-slip mengembalikan JSON jika diminta.
     */
    public function test_void_slip_route_returns_json(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-VOID-TEST-003',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Citra Walk-in',
            'total_fnb' => 5000.0,
            'gross_amount' => 5000.0,
            'net_amount' => 5000.0,
            'payment_method' => 'cash',
            'payment_status' => 'cancelled',
            'void_by' => $this->supervisor->id,
            'void_reason' => 'Salah input menu',
            'void_at' => Carbon::now(),
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->getJson(route('presensi2.penjualan.void-slip', ['sale' => $sale->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'void_data' => [
                'invoice_number' => 'INV-VOID-TEST-003',
                'customer_name' => 'Citra Walk-in',
                'reason' => 'Salah input menu',
            ],
        ]);
    }

    /**
     * Test 4: Halaman POS Desktop memuat modal struk void thermal dan tombol Cetak Slip VOID.
     */
    public function test_pos_desktop_page_contains_void_modal_and_void_slip_button(): void
    {
        // Buat 1 nota yang sudah dibatalkan
        CafeSale::create([
            'invoice_number' => 'INV-VOID-TEST-004',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Dedi User',
            'total_fnb' => 5000.0,
            'gross_amount' => 5000.0,
            'net_amount' => 5000.0,
            'payment_method' => 'cash',
            'payment_status' => 'cancelled',
            'void_by' => $this->supervisor->id,
            'void_reason' => 'Salah harga',
            'void_at' => Carbon::now(),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('pos'));

        $response->assertStatus(200);
        $response->assertSee('id="modal-void-receipt"', false);
        $response->assertSee('id="printable-void-area"', false);
        $response->assertSee('STRUK PEMBATALAN (VOID)');
        $response->assertSee('Slip VOID');
    }
}
