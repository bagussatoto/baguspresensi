<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\StockOpname;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class AdminStockOpnameTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Warehouse $warehouse;

    protected ProductVariant $variantA;

    protected ProductVariant $variantB;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $product = Product::create([
            'code' => 'PRD-OPN-TEST',
            'name' => 'Produk Opname Test',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'code' => 'OPN-TEST',
            'name' => 'Cabang Audit Opname Test',
            'type' => BranchType::Store,
            'address' => 'Jl. Audit Opname',
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'name' => 'Gudang Utama OPN Test',
            'code' => 'WH-OPN-TEST',
            'is_active' => true,
        ]);

        $this->variantA = ProductVariant::create([
            'sku' => 'SKU-OPN-001',
            'product_id' => $product->id,
            'name' => 'Varian Opname A',
            'cost_price' => 20000,
            'base_price' => 30000,
            'is_active' => true,
        ]);

        $this->variantB = ProductVariant::create([
            'sku' => 'SKU-OPN-002',
            'product_id' => $product->id,
            'name' => 'Varian Opname B',
            'cost_price' => 50000,
            'base_price' => 75000,
            'is_active' => true,
        ]);

        // Pasang stok awal di warehouse
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variantA->id,
            'qty_on_hand' => 100,
            'safety_stock' => 10,
        ]);

        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variantB->id,
            'qty_on_hand' => 50,
            'safety_stock' => 5,
        ]);

        // Lokasi rak
        BranchProduct::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variantA->id,
            'bin_location' => 'Rak-A1',
            'reorder_point' => 15,
        ]);

        BranchProduct::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variantB->id,
            'bin_location' => 'Rak-B2',
            'reorder_point' => 10,
        ]);

        $this->employee = Employee::create([
            'code' => 'EMP-OPN-AUDITOR',
            'name' => 'Auditor Lapangan Opname',
            'email' => 'auditor@example.com',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_can_render_stock_opname_index_page(): void
    {
        $response = $this->get(route('admin.cabang.opname'));

        $response->assertOk();
        $response->assertSee('Stock Opname Berkala');
        $response->assertSee('Total Sesi Audit');
    }

    public function test_can_initiate_new_stock_opname_session(): void
    {
        $response = $this->post(route('admin.cabang.opname.store'), [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'opname_date' => now()->toDateString(),
            'notes' => 'Uji coba inisiasi audit akhir pekan.',
        ]);

        $opname = StockOpname::where('branch_id', $this->branch->id)->latest('id')->first();
        $this->assertNotNull($opname);
        $this->assertEquals('draft', $opname->status);
        $this->assertGreaterThanOrEqual(2, $opname->total_items_counted);

        $response->assertRedirect(route('admin.cabang.opname.show', $opname->id));
        $response->assertSessionHas('success');
    }

    public function test_can_view_stock_opname_sheet_and_update_physical_counts(): void
    {
        // 1. Inisiasi sesi
        $this->post(route('admin.cabang.opname.store'), [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'opname_date' => now()->toDateString(),
        ]);

        $opname = StockOpname::where('branch_id', $this->branch->id)->latest('id')->first();
        $this->assertNotNull($opname);

        // 2. Akses halaman show
        $showResponse = $this->get(route('admin.cabang.opname.show', $opname->id));
        $showResponse->assertOk();
        $showResponse->assertSee($opname->opname_number);
        $showResponse->assertSee('Rak-A1');
        $showResponse->assertSee('Rak-B2');

        // 3. Masukkan hasil hitung fisik (Ada selisih: variantA -5, variantB +2)
        $itemA = $opname->items()->where('product_variant_id', $this->variantA->id)->first();
        $itemB = $opname->items()->where('product_variant_id', $this->variantB->id)->first();

        $updateResponse = $this->post(route('admin.cabang.opname.update-counts', $opname->id), [
            'conducted_by' => $this->employee->id,
            'notes' => 'Selesai dihitung oleh auditor.',
            'items' => [
                [
                    'id' => $itemA->id,
                    'qty_physical' => 95, // sistem 100 => selisih -5
                    'notes' => 'Hilang/rusak 5 unit',
                ],
                [
                    'id' => $itemB->id,
                    'qty_physical' => 52, // sistem 50 => selisih +2
                    'notes' => 'Surplus fisik 2 unit',
                ],
            ],
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $opname->refresh();
        $this->assertEquals('completed', $opname->status);
        $this->assertEquals(2, $opname->total_discrepancy_items);

        $itemA->refresh();
        $this->assertEquals(95, $itemA->qty_physical);
        $this->assertEquals(-5, $itemA->qty_variance);
        $this->assertEquals(-100000.0, (float) $itemA->total_variance_cost); // -5 * 20000

        $itemB->refresh();
        $this->assertEquals(52, $itemB->qty_physical);
        $this->assertEquals(2, $itemB->qty_variance);
        $this->assertEquals(100000.0, (float) $itemB->total_variance_cost); // +2 * 50000
    }

    public function test_can_authorize_and_adjust_stock_inventory(): void
    {
        // Inisiasi sesi & input hitung fisik
        $this->post(route('admin.cabang.opname.store'), [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'opname_date' => now()->toDateString(),
        ]);

        $opname = StockOpname::where('branch_id', $this->branch->id)->latest('id')->first();
        $itemA = $opname->items()->where('product_variant_id', $this->variantA->id)->first();

        $this->post(route('admin.cabang.opname.update-counts', $opname->id), [
            'conducted_by' => $this->employee->id,
            'items' => [
                [
                    'id' => $itemA->id,
                    'qty_physical' => 88, // sistem 100 => disesuaikan jadi 88
                ],
            ],
        ]);

        // Supervisor mengesahkan adjustment
        $adjustResponse = $this->post(route('admin.cabang.opname.adjust', $opname->id), [
            'approved_by' => $this->employee->id,
        ]);

        $adjustResponse->assertRedirect();
        $adjustResponse->assertSessionHas('success');

        $opname->refresh();
        $this->assertEquals('adjusted', $opname->status);
        $this->assertNotNull($opname->approved_at);

        // Verifikasi inventaris fisik gudang cabang benar-benar terupdate menjadi 88
        $stockA = StockInventory::where('warehouse_id', $this->warehouse->id)
            ->where('product_variant_id', $this->variantA->id)
            ->first();

        $this->assertEquals(88, $stockA->qty_on_hand);

        // Verifikasi Jurnal Umum SAK Otomatis Penyesuaian Persediaan
        $opname->load(['journalEntry.lines.chartOfAccount']);
        $journal = $opname->journalEntry;
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertStringStartsWith('JRN-ADJ', $journal->entry_number);

        // Nilai selisih: 88 - 100 = -12 unit * Rp 20.000 = Rp 240.000 (Defisit)
        $this->assertEquals(240000.0, (float) $journal->total_debit);
        $this->assertEquals(240000.0, (float) $journal->total_credit);

        // Cek baris jurnal: Debit Beban Selisih (6-10006), Kredit Persediaan (1-13001)
        $debitLine = $journal->lines->where('debit', '>', 0)->first();
        $creditLine = $journal->lines->where('credit', '>', 0)->first();

        $this->assertEquals('6-10006', $debitLine->chartOfAccount->account_code);
        $this->assertEquals(240000.0, (float) $debitLine->debit);

        $this->assertEquals('1-13001', $creditLine->chartOfAccount->account_code);
        $this->assertEquals(240000.0, (float) $creditLine->credit);

        // Verifikasi halaman lembar audit menampilkan informasi Jurnal Akuntansi SAK
        $showResponse = $this->get(route('admin.cabang.opname.show', $opname->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Jurnal Umum Penyesuaian Persediaan');
        $showResponse->assertSee($journal->entry_number);
    }

    public function test_can_journal_surplus_physical_inventory(): void
    {
        // Inisiasi sesi & input hitung fisik surplus (+5 unit variantB @ Rp 50.000 = Rp 250.000)
        $this->post(route('admin.cabang.opname.store'), [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'opname_date' => now()->toDateString(),
        ]);

        $opname = StockOpname::where('branch_id', $this->branch->id)->latest('id')->first();
        $itemB = $opname->items()->where('product_variant_id', $this->variantB->id)->first();

        $this->post(route('admin.cabang.opname.update-counts', $opname->id), [
            'conducted_by' => $this->employee->id,
            'items' => [
                [
                    'id' => $itemB->id,
                    'qty_physical' => 55, // sistem 50 => surplus +5
                ],
            ],
        ]);

        $this->post(route('admin.cabang.opname.adjust', $opname->id), [
            'approved_by' => $this->employee->id,
        ]);

        $opname->refresh();
        $journal = $opname->journalEntry;

        $this->assertNotNull($journal);
        $this->assertEquals(250000.0, (float) $journal->total_debit);
        $this->assertEquals(250000.0, (float) $journal->total_credit);

        // Surplus: Debit Persediaan (1-13001), Kredit Pendapatan Selisih (4-20001)
        $debitLine = $journal->lines->where('debit', '>', 0)->first();
        $creditLine = $journal->lines->where('credit', '>', 0)->first();

        $this->assertEquals('1-13001', $debitLine->chartOfAccount->account_code);
        $this->assertEquals('4-20001', $creditLine->chartOfAccount->account_code);
    }

    public function test_can_export_stock_opname_to_csv(): void
    {
        $this->post(route('admin.cabang.opname.store'), [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'opname_date' => now()->toDateString(),
        ]);

        $opname = StockOpname::where('branch_id', $this->branch->id)->latest('id')->first();

        $response = $this->get(route('admin.cabang.opname.export-csv', $opname->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('BERITA ACARA AUDIT FISIK', $response->streamedContent());
        $this->assertStringContainsString($opname->opname_number, $response->streamedContent());
    }
}
