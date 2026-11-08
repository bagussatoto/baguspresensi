<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Kas\Domain\Services\AutoJournalService;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\ProductPricing;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\StockOpname;
use Modules\MasterData\Domain\Models\StockOpnameItem;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Pengelolaan Audit Fisik Persediaan & Stock Opname Berkala Multi-Cabang.
 */
class AdminStockOpnameController extends Controller
{
    public function __construct(
        protected AutoJournalService $autoJournalService
    ) {}

    /**
     * 1. Daftar Riwayat Sesi Stock Opname Seluruh Jaringan Cabang.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = StockOpname::with(['branch', 'warehouse', 'conductor', 'approver'])
            ->withCount('items')
            ->latest('id');

        if ($request->filled('branch_id') && $request->input('branch_id') !== 'all') {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $opnames = $query->paginate(15);
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total_sessions' => StockOpname::count(),
            'draft_sessions' => StockOpname::where('status', 'draft')->count(),
            'pending_approval' => StockOpname::where('status', 'completed')->count(),
            'adjusted_sessions' => StockOpname::where('status', 'adjusted')->count(),
            'total_variance_cost' => (float) StockOpname::where('status', 'adjusted')->sum('total_variance_cost'),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'opnames' => $opnames,
                'stats' => $stats,
            ]);
        }

        return view('admin.cabang.opname.index', compact('opnames', 'branches', 'stats'));
    }

    /**
     * 2. Inisiasi Sesi Stock Opname Baru untuk Cabang & Gudang.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $branch = Branch::with('warehouses')->findOrFail($validated['branch_id']);
        $warehouseId = $validated['warehouse_id'] ?? $branch->warehouses->first()?->id;

        if (! $warehouseId) {
            $msg = "Cabang {$branch->name} belum memiliki gudang penyimpanan.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $opnameDate = Carbon::parse($validated['opname_date']);
        $opnameNumber = sprintf('SOP-%s-%s-%s', $branch->code, $opnameDate->format('Ymd'), strtoupper(substr(uniqid(), -4)));

        try {
            DB::beginTransaction();

            $opname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouseId,
                'opname_date' => $opnameDate->toDateString(),
                'status' => 'draft',
                'notes' => $validated['notes'] ?? 'Audit fisik persediaan berkala.',
            ]);

            // Ambil seluruh stok varian yang ada di gudang ini
            $inventories = StockInventory::with('variant')
                ->where('warehouse_id', $warehouseId)
                ->get();

            // Peta lokasi rak dari BranchProduct
            $branchProducts = BranchProduct::where('branch_id', $branch->id)
                ->pluck('bin_location', 'product_variant_id')
                ->toArray();

            // Peta HPP zonasi dari ProductPricing
            $branchPricings = ProductPricing::where('branch_id', $branch->id)
                ->pluck('base_cost', 'product_variant_id')
                ->toArray();

            $itemsCreated = 0;
            foreach ($inventories as $inv) {
                if (! $inv->variant) {
                    continue;
                }

                $binLoc = $branchProducts[$inv->product_variant_id] ?? 'Gudang Utama';
                $cost = (float) ($branchPricings[$inv->product_variant_id] ?? $inv->variant->cost_price ?? 0.0);

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_variant_id' => $inv->product_variant_id,
                    'bin_location' => $binLoc,
                    'qty_system' => (int) $inv->qty_on_hand,
                    'qty_physical' => (int) $inv->qty_on_hand, // default sama dengan sistem
                    'qty_variance' => 0,
                    'unit_cost' => $cost,
                    'total_variance_cost' => 0.0,
                ]);

                $itemsCreated++;
            }

            $opname->update(['total_items_counted' => $itemsCreated]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Sesi audit stock opname {$opnameNumber} berhasil dimulai dengan {$itemsCreated} item SKU.",
                    'opname' => $opname->fresh(['items']),
                ]);
            }

            return redirect()->route('admin.cabang.opname.show', $opname->id)
                ->with('success', "Sesi audit fisik stock opname {$opnameNumber} berhasil dibuka dengan {$itemsCreated} item SKU.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Stock Opname creation error: '.$e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal membuka sesi stock opname: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal membuka sesi stock opname: '.$e->getMessage());
        }
    }

    /**
     * 3. Tampilkan Lembar Audit Hitung Fisik per Rak (Stock Opname Sheet).
     */
    public function show(Request $request, StockOpname $stockOpname): View|JsonResponse
    {
        $stockOpname->load(['branch', 'warehouse', 'conductor', 'approver', 'items.variant.product', 'journalEntry.lines.chartOfAccount']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'opname' => $stockOpname,
            ]);
        }

        return view('admin.cabang.opname.show', compact('stockOpname'));
    }

    /**
     * 4. Simpan Hasil Hitung Fisik Operator per Rak & Hitung Varian Selisih.
     */
    public function updateCounts(Request $request, StockOpname $stockOpname): RedirectResponse|JsonResponse
    {
        if ($stockOpname->isAdjusted()) {
            $msg = 'Sesi audit ini telah diotorisasi dan disesuaikan ke inventaris. Tidak dapat diubah lagi.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:stock_opname_items,id'],
            'items.*.qty_physical' => ['required', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'conducted_by' => ['nullable', 'exists:employees,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::beginTransaction();

            $totalCounted = 0;
            $discrepancyCount = 0;
            $totalVarianceCost = 0.0;

            foreach ($validated['items'] as $itemData) {
                $item = StockOpnameItem::where('stock_opname_id', $stockOpname->id)->find($itemData['id']);
                if (! $item) {
                    continue;
                }

                $qtyPhysical = (int) $itemData['qty_physical'];
                $qtyVariance = $qtyPhysical - (int) $item->qty_system;
                $varianceCost = $qtyVariance * (float) $item->unit_cost;

                $item->update([
                    'qty_physical' => $qtyPhysical,
                    'qty_variance' => $qtyVariance,
                    'total_variance_cost' => $varianceCost,
                    'notes' => $itemData['notes'] ?? $item->notes,
                ]);

                $totalCounted++;
                if ($qtyVariance !== 0) {
                    $discrepancyCount++;
                }
                $totalVarianceCost += $varianceCost;
            }

            $stockOpname->update([
                'total_items_counted' => $totalCounted,
                'total_discrepancy_items' => $discrepancyCount,
                'total_variance_cost' => $totalVarianceCost,
                'conducted_by' => $validated['conducted_by'] ?? $stockOpname->conducted_by,
                'notes' => $validated['notes'] ?? $stockOpname->notes,
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            DB::commit();

            $msg = "Hasil hitung fisik {$stockOpname->opname_number} berhasil disimpan ({$discrepancyCount} SKU memiliki selisih).";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'opname' => $stockOpname->fresh(['items']),
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Stock Opname update error: '.$e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan hasil hitung: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal menyimpan hasil hitung: '.$e->getMessage());
        }
    }

    /**
     * 5. Otorisasi Supervisor & Terapkan Penyesuaian Stok Fisik (Stock Adjustment).
     */
    public function adjust(Request $request, StockOpname $stockOpname): RedirectResponse|JsonResponse
    {
        if ($stockOpname->isAdjusted()) {
            $msg = 'Sesi opname ini sudah pernah diotorisasi dan disesuaikan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'approved_by' => ['nullable', 'exists:employees,id'],
            'supervisor_pin' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $stockOpname->load('items');

            // Sesuaikan kuantitas fisik di StockInventory gudang cabang
            foreach ($stockOpname->items as $item) {
                if ($item->qty_variance !== 0) {
                    $stock = StockInventory::firstOrCreate(
                        ['warehouse_id' => $stockOpname->warehouse_id, 'product_variant_id' => $item->product_variant_id],
                        ['qty_on_hand' => $item->qty_system, 'safety_stock' => 10]
                    );

                    // Update stok fisik menjadi persis hasil hitung fisik riil
                    $stock->qty_on_hand = (int) $item->qty_physical;
                    $stock->save();
                }
            }

            $approverId = $validated['approved_by'] ?? Employee::first()?->id;

            $stockOpname->update([
                'status' => 'adjusted',
                'approved_by' => $approverId,
                'approved_at' => Carbon::now(),
            ]);

            // Bukukan Jurnal Umum SAK Penyesuaian Nilai Persediaan Otomatis (Double-Entry GL)
            $journal = $this->autoJournalService->journalStockOpnameAdjustment($stockOpname);

            DB::commit();

            $msg = "Penyesuaian stok opname {$stockOpname->opname_number} berhasil disetujui! Stok fisik inventaris gudang cabang telah diperbarui.";
            if ($journal) {
                $msg .= " Jurnal penyesuaian otomatis {$journal->entry_number} telah dibukukan ke Buku Besar.";
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'opname' => $stockOpname->fresh(['items', 'journalEntry.lines.chartOfAccount']),
                    'journal' => $journal,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Stock Opname adjust error: '.$e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menerapkan penyesuaian stok: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal menerapkan penyesuaian stok: '.$e->getMessage());
        }
    }

    /**
     * 6. Unduh Berita Acara Hasil Hitung Fisik Stock Opname ke CSV Excel.
     */
    public function exportCsv(StockOpname $stockOpname): StreamedResponse
    {
        $stockOpname->load(['branch', 'warehouse', 'conductor', 'approver', 'items.variant.product']);

        $fileName = 'berita_acara_opname_'.$stockOpname->opname_number.'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($stockOpname): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['=== BERITA ACARA AUDIT FISIK & STOCK OPNAME PERSADA PT MAYA GRAHA KENCANA ===']);
            fputcsv($handle, ['No. Dokumen Opname:', $stockOpname->opname_number]);
            fputcsv($handle, ['Cabang:', $stockOpname->branch->name ?? '-']);
            fputcsv($handle, ['Gudang:', $stockOpname->warehouse->name ?? '-']);
            fputcsv($handle, ['Tanggal Audit:', $stockOpname->opname_date->format('d/m/Y')]);
            fputcsv($handle, ['Status:', strtoupper($stockOpname->status)]);
            fputcsv($handle, ['Petugas Auditor:', $stockOpname->conductor->name ?? '-']);
            fputcsv($handle, ['Supervisor Pengesah:', $stockOpname->approver->name ?? '-']);
            fputcsv($handle, ['Total SKU Dihitung:', $stockOpname->total_items_counted]);
            fputcsv($handle, ['Total SKU Selisih:', $stockOpname->total_discrepancy_items]);
            fputcsv($handle, ['Total Nilai Selisih (Rp):', number_format($stockOpname->total_variance_cost, 2, ',', '.')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'No.',
                'SKU Produk',
                'Nama Varian Produk',
                'Lokasi Rak (Bin)',
                'Stok Sistem (Buku)',
                'Hasil Hitung Fisik (Rak)',
                'Selisih Unit (Variance)',
                'Estimasi HPP Satuan (Rp)',
                'Total Nilai Selisih (Rp)',
                'Status Selisih',
                'Catatan Lapangan',
            ]);

            $no = 1;
            foreach ($stockOpname->items as $item) {
                $statusLabel = 'SESUAI (NIHIL)';
                if ($item->qty_variance > 0) {
                    $statusLabel = 'LEBIH (+'.$item->qty_variance.')';
                } elseif ($item->qty_variance < 0) {
                    $statusLabel = 'KURANG ('.$item->qty_variance.')';
                }

                fputcsv($handle, [
                    $no++,
                    $item->variant->sku ?? '-',
                    $item->variant->name ?? '-',
                    $item->bin_location ?? '-',
                    $item->qty_system,
                    $item->qty_physical,
                    $item->qty_variance,
                    number_format($item->unit_cost, 2, ',', '.'),
                    number_format($item->total_variance_cost, 2, ',', '.'),
                    $statusLabel,
                    $item->notes ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
