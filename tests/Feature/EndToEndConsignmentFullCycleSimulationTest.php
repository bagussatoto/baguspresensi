<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Models\JournalEntry;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Consignment;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\Supplier;
use Tests\TestCase;

/**
 * Simulasi E2E Alur Konsinyasi Lengkap Multi-Cabang (End-to-End Full Lifecycle):
 * 1. Pendaftaran Titipan Masuk Vendor (Inward Consignment).
 * 2. Transaksi Laku & Setoran Pelunasan Hak Vendor beserta Jurnal SAK.
 * 3. Pendaftaran Titipan Keluar ke Mitra Reseller (Outward Consignment).
 * 4. Rekonsiliasi Bertahap Penjualan Mitra & Penerimaan Kas beserta Jurnal SAK.
 * 5. Pelunasan Penuh (Settled & Closed) dan Pemantauan Status Neraca/Aging.
 * 6. Verifikasi Ekspor Berita Acara & Konsolidasi Cabang.
 */
class EndToEndConsignmentFullCycleSimulationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $storeBranch;

    private Supplier $vendorSnack;

    private Customer $resellerMitra;

    private ProductVariant $keripikVariant;

    private ProductVariant $kopiVariant;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Cabang Ritel
        $this->storeBranch = Branch::create([
            'code' => 'STORE-MLG',
            'name' => 'Outlet Cabang Malang',
            'type' => BranchType::Store,
            'address' => 'Jl. Ijen No. 45 Malang',
            'is_active' => true,
        ]);

        // 2. Setup Vendor Supplier Konsinyasi Masuk
        $this->vendorSnack = Supplier::create([
            'code' => 'VEND-KRPK-01',
            'name' => 'UMKM Keripik Apel Batu',
            'phone' => '081234567890',
            'payment_term_days' => 14,
            'is_active' => true,
        ]);

        // 3. Setup Mitra Reseller Konsinyasi Keluar
        $this->resellerMitra = Customer::create([
            'code' => 'CUST-RSL-007',
            'name' => 'Kantin Kampus UB Malang',
            'customer_type' => CustomerType::RESELLER,
            'credit_limit' => 5000000,
            'current_credit_used' => 0,
            'payment_term_days' => 7,
            'is_active' => true,
        ]);

        // 4. Setup Produk & Varian
        $productA = Product::create([
            'code' => 'PRD-APL-01',
            'name' => 'Keripik Apel Super Premium',
            'is_active' => true,
        ]);

        $this->keripikVariant = ProductVariant::create([
            'product_id' => $productA->id,
            'sku' => 'SKU-APL-100G',
            'name' => 'Keripik Apel 100gr',
            'cost_price' => 10000.0,
            'base_price' => 15000.0,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'code' => 'PRD-KPI-02',
            'name' => 'Kopi Robusta Dampit Sachet',
            'is_active' => true,
        ]);

        $this->kopiVariant = ProductVariant::create([
            'product_id' => $productB->id,
            'sku' => 'SKU-KPI-250G',
            'name' => 'Kopi Robusta 250gr',
            'cost_price' => 20000.0,
            'base_price' => 30000.0,
            'is_active' => true,
        ]);
    }

    public function test_full_e2e_consignment_cycle_inward_and_outward(): void
    {
        // =========================================================================
        // TAHAP 1: PENDAFTARAN TITIPAN MASUK DARI VENDOR (INWARD CONSIGNMENT)
        // =========================================================================
        // Vendor menitipkan 100 pcs Keripik Apel @ Rp 10.000 = Rp 1.000.000 di Toko Malang
        $inwardPayload = [
            'branch_id' => $this->storeBranch->id,
            'type' => 'inward',
            'partner_name' => $this->vendorSnack->name,
            'supplier_id' => $this->vendorSnack->id,
            'start_date' => Carbon::today()->toDateString(),
            'due_settlement_date' => Carbon::today()->addDays(14)->toDateString(),
            'notes' => 'Titipan perdana 100 bungkus Keripik Apel di etalase depan kasir.',
            'items' => [
                [
                    'product_variant_id' => $this->keripikVariant->id,
                    'unit_price' => 10000.0,
                    'qty_consigned' => 100,
                ],
            ],
        ];

        $inwardStoreResponse = $this->post(route('admin.cabang.consignments.store'), $inwardPayload);
        $inwardStoreResponse->assertRedirect();
        $inwardStoreResponse->assertSessionHas('success');

        $inwardConsignment = Consignment::where('branch_id', $this->storeBranch->id)
            ->where('type', 'inward')
            ->first();

        $this->assertNotNull($inwardConsignment);
        $this->assertStringStartsWith('CSG-IN', $inwardConsignment->consignment_number);
        $this->assertEquals(1000000.0, (float) $inwardConsignment->total_consigned_value);
        $this->assertEquals(0.0, (float) $inwardConsignment->total_sold_value);
        $this->assertEquals(0.0, (float) $inwardConsignment->total_settled_amount);
        $this->assertEquals('active', $inwardConsignment->status);

        $inwardItem = $inwardConsignment->items()->first();
        $this->assertEquals(100, $inwardItem->qty_consigned);
        $this->assertEquals(100, $inwardItem->qty_remaining);
        $this->assertEquals(0, $inwardItem->qty_sold);

        // =========================================================================
        // TAHAP 2: REKONSILIASI PENJUALAN TOKO & SETORAN KE VENDOR (INWARD SETTLEMENT)
        // =========================================================================
        // Di toko cabang, 60 pcs keripik apel laku terjual ke konsumen kasir.
        // Toko membayarkan hak setoran vendor sebesar 60 pcs * Rp 10.000 = Rp 600.000
        $inwardSettleResponse = $this->post(route('admin.cabang.consignments.settle', $inwardConsignment->id), [
            'settled_amount' => 600000.0,
            'notes' => 'Setoran termin 1: laku 60 bungkus dibayar tunai ke sales vendor.',
            'items' => [
                [
                    'id' => $inwardItem->id,
                    'qty_sold_now' => 60,
                ],
            ],
        ]);

        $inwardSettleResponse->assertRedirect();
        $inwardSettleResponse->assertSessionHas('success');

        $inwardConsignment->refresh();
        $inwardItem->refresh();

        $this->assertEquals(600000.0, (float) $inwardConsignment->total_sold_value);
        $this->assertEquals(600000.0, (float) $inwardConsignment->total_settled_amount);
        $this->assertEquals(0.0, (float) $inwardConsignment->remaining_settlement_due);
        $this->assertEquals(60, $inwardItem->qty_sold);
        $this->assertEquals(40, $inwardItem->qty_remaining); // Sisa fisik 40 bungkus di rak toko

        // Verifikasi Jurnal Umum SAK Inward:
        // Debit: 2-10003 (Hutang Konsinyasi Supplier) Rp 600.000
        // Kredit: 1-10001 (Kas Laci Kasir Outlet) Rp 600.000
        $inwardJournal = JournalEntry::where('reference_type', Consignment::class)
            ->where('reference_id', $inwardConsignment->id)
            ->with(['lines.chartOfAccount'])
            ->first();

        $this->assertNotNull($inwardJournal);
        $this->assertEquals('posted', $inwardJournal->status);
        $this->assertEquals(600000.0, (float) $inwardJournal->total_debit);
        $this->assertEquals(600000.0, (float) $inwardJournal->total_credit);

        $inwardDebit = $inwardJournal->lines->where('debit', '>', 0)->first();
        $inwardCredit = $inwardJournal->lines->where('credit', '>', 0)->first();
        $this->assertEquals('2-10003', $inwardDebit->chartOfAccount->account_code);
        $this->assertEquals('1-10001', $inwardCredit->chartOfAccount->account_code);

        // =========================================================================
        // TAHAP 3: PENDAFTARAN TITIPAN KELUAR KE MITRA RESELLER (OUTWARD CONSIGNMENT)
        // =========================================================================
        // Toko Malang menitipkan 50 sachet Kopi Robusta @ Rp 25.000 = Rp 1.250.000 ke Kantin Kampus UB
        $outwardPayload = [
            'branch_id' => $this->storeBranch->id,
            'type' => 'outward',
            'partner_name' => $this->resellerMitra->name,
            'customer_id' => $this->resellerMitra->id,
            'start_date' => Carbon::today()->toDateString(),
            'due_settlement_date' => Carbon::today()->addDays(7)->toDateString(),
            'notes' => 'Titipan konsinyasi 50 sachet kopi robusta ke Kantin UB.',
            'items' => [
                [
                    'product_variant_id' => $this->kopiVariant->id,
                    'unit_price' => 25000.0,
                    'qty_consigned' => 50,
                ],
            ],
        ];

        $outwardStoreResponse = $this->post(route('admin.cabang.consignments.store'), $outwardPayload);
        $outwardStoreResponse->assertRedirect();
        $outwardStoreResponse->assertSessionHas('success');

        $outwardConsignment = Consignment::where('branch_id', $this->storeBranch->id)
            ->where('type', 'outward')
            ->first();

        $this->assertNotNull($outwardConsignment);
        $this->assertStringStartsWith('CSG-OUT', $outwardConsignment->consignment_number);
        $this->assertEquals(1250000.0, (float) $outwardConsignment->total_consigned_value);
        $this->assertEquals(0.0, (float) $outwardConsignment->total_sold_value);
        $this->assertEquals('active', $outwardConsignment->status);

        $outwardItem = $outwardConsignment->items()->first();
        $this->assertEquals(50, $outwardItem->qty_remaining);

        // =========================================================================
        // TAHAP 4: REKONSILIASI BERTAHAP DARI MITRA RESELLER (OUTWARD SETTLEMENT 1)
        // =========================================================================
        // Minggu ke-1: Mitra UB menyetor hasil penjualan 30 sachet * Rp 25.000 = Rp 750.000
        $outwardSettleResponse1 = $this->post(route('admin.cabang.consignments.settle', $outwardConsignment->id), [
            'settled_amount' => 750000.0,
            'notes' => 'Setoran termin 1: laku 30 sachet via transfer bank.',
            'items' => [
                [
                    'id' => $outwardItem->id,
                    'qty_sold_now' => 30,
                ],
            ],
        ]);

        $outwardSettleResponse1->assertRedirect();
        $outwardConsignment->refresh();
        $outwardItem->refresh();

        $this->assertEquals(750000.0, (float) $outwardConsignment->total_sold_value);
        $this->assertEquals(750000.0, (float) $outwardConsignment->total_settled_amount);
        $this->assertEquals(0.0, (float) $outwardConsignment->remaining_settlement_due);
        $this->assertEquals(30, $outwardItem->qty_sold);
        $this->assertEquals(20, $outwardItem->qty_remaining); // Sisa 20 sachet di mitra

        // Verifikasi Jurnal Umum SAK Outward:
        // Debit: 1-10001 (Kas Laci Kasir / Bank) Rp 750.000
        // Kredit: 4-10001 (Pendapatan Penjualan Konsinyasi) Rp 750.000
        $outwardJournal1 = JournalEntry::where('reference_type', Consignment::class)
            ->where('reference_id', $outwardConsignment->id)
            ->with(['lines.chartOfAccount'])
            ->first();

        $this->assertNotNull($outwardJournal1);
        $this->assertEquals(750000.0, (float) $outwardJournal1->total_debit);
        $this->assertEquals(750000.0, (float) $outwardJournal1->total_credit);

        $outwardDebit = $outwardJournal1->lines->where('debit', '>', 0)->first();
        $outwardCredit = $outwardJournal1->lines->where('credit', '>', 0)->first();
        $this->assertEquals('1-10001', $outwardDebit->chartOfAccount->account_code);
        $this->assertEquals('4-10001', $outwardCredit->chartOfAccount->account_code);

        // =========================================================================
        // TAHAP 5: PELUNASAN PENUH SISA STOK MITRA (SETTLED & CLOSED)
        // =========================================================================
        // Minggu ke-2: Sisa 20 sachet terjual habis. Mitra menyetor Rp 500.000 (Lunas penuh)
        $outwardSettleResponse2 = $this->post(route('admin.cabang.consignments.settle', $outwardConsignment->id), [
            'settled_amount' => 500000.0,
            'notes' => 'Setoran termin 2: sisa 20 sachet habis terjual. Kontrak tuntas.',
            'items' => [
                [
                    'id' => $outwardItem->id,
                    'qty_sold_now' => 20,
                ],
            ],
        ]);

        $outwardSettleResponse2->assertRedirect();
        $outwardConsignment->refresh();
        $outwardItem->refresh();

        $this->assertEquals(1250000.0, (float) $outwardConsignment->total_sold_value);
        $this->assertEquals(1250000.0, (float) $outwardConsignment->total_settled_amount);
        $this->assertEquals(0.0, (float) $outwardConsignment->remaining_settlement_due);
        $this->assertEquals(50, $outwardItem->qty_sold);
        $this->assertEquals(0, $outwardItem->qty_remaining); // Habis
        $this->assertEquals('settled', $outwardConsignment->status); // Status otomatis menjadi SETTLED

        // Memastikan terbentuk 2 jurnal mutasi yang akurat untuk kontrak outward ini
        $outwardJournals = JournalEntry::where('reference_type', Consignment::class)
            ->where('reference_id', $outwardConsignment->id)
            ->get();
        $this->assertCount(2, $outwardJournals);

        // =========================================================================
        // TAHAP 6: VERIFIKASI DASHBOARD & EKSPOR CSV BERITA ACARA RESMI
        // =========================================================================
        $dashboardResponse = $this->get(route('admin.cabang.aging'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Outlet Cabang Malang');
        $dashboardResponse->assertSee($inwardConsignment->consignment_number);
        $dashboardResponse->assertSee($outwardConsignment->consignment_number);
        $dashboardResponse->assertSee('LUNAS / SETTLED');

        // Unduh CSV Konsinyasi
        $csvResponse = $this->get(route('admin.cabang.consignments.export'));
        $csvResponse->assertOk();
        $csvContent = $csvResponse->streamedContent();

        $this->assertStringContainsString('LAPORAN MONITORING SALDO KONSINYASI MULTI-CABANG', $csvContent);
        $this->assertStringContainsString($inwardConsignment->consignment_number, $csvContent);
        $this->assertStringContainsString($outwardConsignment->consignment_number, $csvContent);
        $this->assertStringContainsString('UMKM Keripik Apel Batu', $csvContent);
        $this->assertStringContainsString('Kantin Kampus UB Malang', $csvContent);
    }
}
