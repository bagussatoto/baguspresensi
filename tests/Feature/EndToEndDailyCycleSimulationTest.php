<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use App\Models\ShiftHandoverChecklist;
use App\Models\ShiftSalesSummary;
use App\Models\ShiftStockRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Models\JournalEntry;
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

/**
 * Uji Coba Simulasi Siklus Operasional Harian Penuh (End-to-End Walkthrough):
 * Kasir POS -> MDM Zonasi & Rak -> Stok ROP Auto-Draft STO -> Rekap Kunci Shift -> Serah Terima -> Jurnal Akuntansi SAK & Pivot.
 */
class EndToEndDailyCycleSimulationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $dc;

    private Branch $store;

    private Warehouse $dcWarehouse;

    private Warehouse $storeWarehouse;

    private Shift $shift1;

    private Shift $shift2;

    private Employee $cashier1;

    private Employee $cashier2;

    private ProductVariant $variant;

    private BranchProduct $branchProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Bagan Akun (COA) SAK untuk Auto-Journal
        ChartOfAccount::create([
            'account_code' => '1-11001',
            'account_name' => 'Kas Laci Kasir POS',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'is_header' => false,
            'is_active' => true,
        ]);

        ChartOfAccount::create([
            'account_code' => '4-10001',
            'account_name' => 'Pendapatan Penjualan F&B',
            'account_category' => AccountCategory::REVENUE,
            'normal_balance' => NormalBalance::CREDIT,
            'is_header' => false,
            'is_active' => true,
        ]);

        // 2. Setup Hierarki Multi-Cabang MDM
        $this->dc = Branch::create([
            'code' => 'DC-PST',
            'name' => 'Distribution Center Pusat Logistik',
            'type' => BranchType::Dc,
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $this->dcWarehouse = Warehouse::create([
            'branch_id' => $this->dc->id,
            'code' => 'WH-DC-01',
            'name' => 'Gudang Pasokan DC Pusat',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $this->store = Branch::create([
            'code' => 'STR-THM',
            'name' => 'Kopi Bagus Thamrin (Store)',
            'type' => BranchType::Store,
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $this->storeWarehouse = Warehouse::create([
            'branch_id' => $this->store->id,
            'code' => 'WH-THM-01',
            'name' => 'Gudang Kasir Toko Thamrin',
            'type' => 'storage',
            'is_active' => true,
        ]);

        // 3. Setup Shift Kerja Kasir
        $this->shift1 = Shift::create([
            'code' => 'SHIFT-1',
            'name' => 'Shift 1 Pagi',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'order' => 1,
            'is_active' => true,
        ]);

        $this->shift2 = Shift::create([
            'code' => 'SHIFT-2',
            'name' => 'Shift 2 Siang',
            'start_time' => '15:00:00',
            'end_time' => '23:00:00',
            'order' => 2,
            'is_active' => true,
        ]);

        // 4. Setup Karyawan Kasir
        $this->cashier1 = Employee::create([
            'branch_id' => $this->store->id,
            'code' => 'EMP-KSR-01',
            'name' => 'Bagus Kasir Shift 1',
            'pin' => Hash::make('123456'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->cashier2 = Employee::create([
            'branch_id' => $this->store->id,
            'code' => 'EMP-KSR-02',
            'name' => 'Andi Kasir Shift 2',
            'pin' => Hash::make('123456'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        // 5. Setup Master Produk, Lokalisasi Spoke, dan Zonasi Harga
        $cat = Category::create([
            'code' => 'CAT-FNB',
            'name' => 'Makanan dan Minuman',
            'slug' => 'fnb',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $cat->id,
            'code' => 'PRD-KOP-GAYO',
            'name' => 'Kopi Arabika Gayo',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-GAYO-250',
            'name' => 'Kopi Arabika Gayo 250gr',
            'base_price' => 20000, // Harga Nasional
            'cost_price' => 10000,
            'is_active' => true,
        ]);

        // Lokalisasi Rak & ROP Cabang Thamrin
        $this->branchProduct = BranchProduct::create([
            'branch_id' => $this->store->id,
            'product_variant_id' => $this->variant->id,
            'bin_location' => 'Rak-A-01',
            'min_stock' => 5,
            'max_stock' => 50,
            'reorder_point' => 10,
            'is_locally_active' => true,
        ]);

        // Zonasi Harga Khusus Cabang Thamrin: Rp 25.000
        ProductPricing::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->store->id,
            'zone_code' => 'ZONE-STR-THM',
            'base_cost' => 12000,
            'sale_price' => 25000,
            'start_date' => Carbon::now()->subDays(2),
            'end_date' => Carbon::now()->addDays(30),
        ]);

        // Stok Fisik Awal: DC = 150 unit, Toko Thamrin = 14 unit
        StockInventory::create([
            'warehouse_id' => $this->dcWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 150,
            'qty_reserved' => 0,
            'safety_stock' => 20,
        ]);

        StockInventory::create([
            'warehouse_id' => $this->storeWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 14,
            'qty_reserved' => 0,
            'safety_stock' => 5,
        ]);
    }

    /**
     * SIMULASI SIKLUS OPERASIONAL LENGKAP:
     * 1. Kasir 1 Clock-in & Cek POS
     * 2. Penjualan POS (Zonasi Harga Rp 25k & Lokasi Rak)
     * 3. Trigger Auto-Draft STO saat Stok <= ROP & Pemenuhan Pasokan dari DC
     * 4. Rekap Shift 1 & Penguncian Penjualan (Freeze Locked)
     * 5. Kasir 2 Login Shift 2 & Verifikasi Serah Terima Shift 1
     * 6. Validasi Jurnal Umum Otomatis & Pivot Akuntansi SAK
     */
    public function test_complete_end_to_end_daily_cycle(): void
    {
        $today = Carbon::today();

        // ==========================================
        // LANGKAH 1: Kasir 1 Clock-In di Shift 1
        // ==========================================
        AttendanceRecord::create([
            'employee_id' => $this->cashier1->id,
            'branch_id' => $this->store->id,
            'shift_id' => $this->shift1->id,
            'work_date' => $today->toDateString(),
            'clock_in_at' => Carbon::now()->subHours(4),
        ]);

        // Kasir 1 membuka POS Desktop -> Melihat harga zonasi Rp 25.000, rak Rak-A-01, stok 14
        $posResponse = $this->withSession([
            'presensi2_employee_id' => $this->cashier1->id,
            'presensi2_branch_id' => $this->store->id,
        ])->get(route('pos'));

        $posResponse->assertStatus(200);
        $posResponse->assertSee('Rak-A-01');
        $posResponse->assertSee('25.000'); // Harga zonasi khusus cabang
        $posResponse->assertSee('14');     // Stok awal toko

        // ==========================================
        // LANGKAH 2: Transaksi Penjualan 6 Unit
        // ==========================================
        // 6 unit x Rp 25.000 = Rp 150.000
        $salePayload = [
            'transaction_type' => 'fnb',
            'payment_method' => 'cash',
            'items' => [
                [
                    'variant_id' => $this->variant->id,
                    'qty' => 6,
                ],
            ],
        ];

        $saleResponse = $this->withSession([
            'presensi2_employee_id' => $this->cashier1->id,
            'presensi2_branch_id' => $this->store->id,
        ])->post(route('presensi2.penjualan.transaksi'), $salePayload);

        $saleResponse->assertRedirect(route('presensi2.penjualan'));

        // Verifikasi Nota Penjualan
        $sale = CafeSale::where('branch_id', $this->store->id)->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(150000, (float) $sale->total_fnb);
        $this->assertEquals(150000, (float) $sale->net_amount);

        // Verifikasi Stok Toko: 14 - 6 = 8 unit
        $storeStock = StockInventory::where('warehouse_id', $this->storeWarehouse->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(8, $storeStock->qty_on_hand);

        // ==========================================
        // LANGKAH 3: Pemicu Auto-Draft STO & Pemenuhan Pasokan dari DC
        // ==========================================
        // Sisa stok 8 <= ROP (10) -> Sistem otomatis terbitkan STO
        // Kuantitas pasok: max_stock (50) - sisa (8) = 42 unit
        $this->assertEquals(1, ShiftStockRequest::where('branch_id', $this->store->id)->count());
        $sto = ShiftStockRequest::where('branch_id', $this->store->id)->first();
        $this->assertEquals(42, $sto->qty_requested);
        $this->assertEquals('pending', $sto->status);
        $this->assertStringContainsString('[AUTO-ROP MDM]', (string) $sto->notes);

        // Petugas DC klik "Penuhi & Kirim dari DC"
        $fulfillResponse = $this->post(route('admin.distribusi.penuhi', $sto->id), [
            'qty_fulfilled' => 42,
        ]);
        $fulfillResponse->assertRedirect(route('admin.distribusi'));

        $sto->refresh();
        $this->assertEquals('fulfilled', $sto->status);

        // Verifikasi Mutasi Gudang:
        // Stok DC berkurang 42 (150 -> 108)
        $dcStock = StockInventory::where('warehouse_id', $this->dcWarehouse->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(108, $dcStock->qty_on_hand);

        // Stok Toko bertambah 42 (8 -> 50)
        $storeStock->refresh();
        $this->assertEquals(50, $storeStock->qty_on_hand);

        // ==========================================
        // LANGKAH 4: Rekap Shift 1 & Penguncian Penjualan (Freeze)
        // ==========================================
        $rekapResponse = $this->withSession([
            'presensi2_employee_id' => $this->cashier1->id,
            'presensi2_branch_id' => $this->store->id,
        ])->post(route('presensi2.rekap.setor'), [
            'deposited_amount' => 150000,
            'retained_drawer_cash' => 100000,
            'deposit_method' => 'cash_collector',
            'deposit_notes' => 'Rekap shift 1 selesai disetor ke kurir kas',
        ]);
        $rekapResponse->assertRedirect(route('presensi2.rekap'));

        // Cek bahwa shift sales terkunci
        $summary = ShiftSalesSummary::where('branch_id', $this->store->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertTrue((bool) $summary->is_sales_locked);
        $this->assertEquals('closed', $summary->status);

        // Uji coba Kasir 1 mencoba transaksi baru di shift 1 yang sudah dikunci -> HARUS DITOLAK
        $blockedResponse = $this->withSession([
            'presensi2_employee_id' => $this->cashier1->id,
            'presensi2_branch_id' => $this->store->id,
        ])->postJson(route('presensi2.penjualan.transaksi'), $salePayload);

        $blockedResponse->assertStatus(422);
        $blockedResponse->assertJsonFragment(['success' => false]);
        $this->assertStringContainsString('Shift ini sudah direkapitulasi dan transaksi penjualan telah DIKUNCI', (string) $blockedResponse->json('message'));

        // ==========================================
        // LANGKAH 5: Serah Terima Shift 2
        // ==========================================
        // Kasir 2 Clock-in di Shift 2
        AttendanceRecord::create([
            'employee_id' => $this->cashier2->id,
            'branch_id' => $this->store->id,
            'shift_id' => $this->shift2->id,
            'work_date' => $today->toDateString(),
            'clock_in_at' => Carbon::now()->subMinute(),
        ]);

        // Kasir 1 telah menyelesaikan checklist shift 1
        $checklist1 = ShiftHandoverChecklist::create([
            'employee_id' => $this->cashier1->id,
            'branch_id' => $this->store->id,
            'shift_id' => $this->shift1->id,
            'check_date' => $today->toDateString(),
            'status' => 'completed',
            'billing_shift_income' => 150000,
        ]);

        // Kasir 2 membuka halaman Serah Terima Desktop -> Sistem menyajikan riwayat penjualan & omset Shift 1
        $handoverResponse = $this->withSession([
            'presensi2_employee_id' => $this->cashier2->id,
            'presensi2_branch_id' => $this->store->id,
        ])->get(route('presensi2.serah-terima.desktop'));

        $handoverResponse->assertStatus(200);
        $handoverResponse->assertSee('Riwayat & Rincian Penjualan Shift Sebelumnya', false);
        $handoverResponse->assertSee('150.000'); // Omset shift 1 terbaca oleh kasir shift 2

        // Kasir 2 simpan serah terima checklist shift 2
        $storeHandoverResponse = $this->withSession([
            'presensi2_employee_id' => $this->cashier2->id,
            'presensi2_branch_id' => $this->store->id,
        ])->post(route('presensi2.serah-terima.store'), [
            'status' => 'completed',
            'notes' => 'Serah terima shift 2 diterima dengan baik.',
        ]);
        $storeHandoverResponse->assertRedirect();

        // ==========================================
        // LANGKAH 6: Validasi Jurnal Umum & Pivot Akuntansi SAK
        // ==========================================
        // Jurnal Umum otomatis terbentuk untuk transaksi kasir Rp 150.000
        $journal = JournalEntry::where('branch_id', $this->store->id)->latest('id')->first();
        $this->assertNotNull($journal);
        $this->assertEquals(150000, (float) $journal->total_debit);
        $this->assertEquals(150000, (float) $journal->total_credit);
        $this->assertEquals('posted', $journal->status);

        // Akses Pivot Table Akuntansi SAK
        $pivotResponse = $this->get(route('admin.akuntansi.pivot', [
            'start_date' => $today->toDateString(),
            'end_date' => $today->toDateString(),
            'row_dim' => 'account',
            'col_dim' => 'branch',
        ]));

        $pivotResponse->assertStatus(200);
        $pivotResponse->assertSee('Pivot Analisis Transaksi SAK');
        $pivotResponse->assertSee('150.000'); // Mutasi akun teragregasi
    }
}
