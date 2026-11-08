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
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Consignment;
use Modules\MasterData\Domain\Models\ConsignmentItem;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\Penjualan\Domain\Models\SalesInvoice;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Laporan Aging Piutang Grosir/B2B & Monitoring Saldo Konsinyasi Multi-Cabang.
 */
class AdminAgingReceivableController extends Controller
{
    public function __construct(
        protected AutoJournalService $autoJournalService
    ) {}

    /**
     * Tampilkan Halaman Utama Laporan Aging Piutang & Saldo Konsinyasi.
     */
    public function index(Request $request): View|JsonResponse
    {
        $selectedBranchId = $request->input('branch_id', 'all');
        $selectedType = $request->input('customer_type', 'all');
        $asOfDate = $request->filled('as_of_date') ? Carbon::parse($request->input('as_of_date')) : Carbon::today();

        // 1. Ambil seluruh piutang belum lunas (SalesInvoice & CafeSale)
        $agingInvoices = $this->getAgingInvoices($selectedBranchId, $selectedType, $asOfDate);
        $customerAgingSummary = $this->aggregateCustomerAging($agingInvoices);

        // KPI Ringkasan Piutang
        $arKpi = [
            'total_receivable' => array_sum(array_column($customerAgingSummary, 'total_outstanding')),
            'current_amount' => array_sum(array_column($customerAgingSummary, 'current')),
            'days_31_60' => array_sum(array_column($customerAgingSummary, 'days_31_60')),
            'days_61_90' => array_sum(array_column($customerAgingSummary, 'days_61_90')),
            'days_over_90' => array_sum(array_column($customerAgingSummary, 'days_over_90')),
            'total_customers' => count($customerAgingSummary),
        ];

        // 2. Ambil data Saldo Konsinyasi Multi-Cabang
        $consignmentQuery = Consignment::with(['branch', 'supplier', 'customer', 'items.variant', 'journalEntries.lines.chartOfAccount'])
            ->latest('id');

        if ($selectedBranchId !== 'all') {
            $consignmentQuery->where('branch_id', $selectedBranchId);
        }

        $consignments = $consignmentQuery->paginate(15, ['*'], 'consign_page');

        $consignmentKpi = [
            'total_contracts' => Consignment::count(),
            'active_contracts' => Consignment::where('status', 'active')->count(),
            'total_consigned_value' => (float) Consignment::where('status', 'active')->sum('total_consigned_value'),
            'total_sold_value' => (float) Consignment::where('status', 'active')->sum('total_sold_value'),
            'remaining_settlement_due' => (float) Consignment::where('status', 'active')->sum('remaining_settlement_due'),
        ];

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $variants = ProductVariant::where('is_active', true)->orderBy('name')->take(100)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'as_of_date' => $asOfDate->toDateString(),
                'ar_kpi' => $arKpi,
                'aging_summary' => $customerAgingSummary,
                'aging_invoices' => $agingInvoices,
                'consignments' => $consignments,
                'consignment_kpi' => $consignmentKpi,
            ]);
        }

        return view('admin.cabang.aging.index', compact(
            'arKpi',
            'customerAgingSummary',
            'agingInvoices',
            'consignments',
            'consignmentKpi',
            'branches',
            'suppliers',
            'customers',
            'variants',
            'asOfDate'
        ));
    }

    /**
     * Inisiasi Kontrak / Titipan Konsinyasi Baru di Cabang.
     */
    public function storeConsignment(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'type' => ['required', 'in:inward,outward'],
            'partner_name' => ['required', 'string', 'max:150'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'start_date' => ['required', 'date'],
            'due_settlement_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.qty_consigned' => ['required', 'integer', 'min:1'],
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        $typePrefix = $validated['type'] === 'inward' ? 'CSG-IN' : 'CSG-OUT';
        $number = sprintf('%s-%s-%s-%s', $typePrefix, $branch->code, date('Ymd'), strtoupper(substr(uniqid(), -4)));

        try {
            DB::beginTransaction();

            $totalConsignedValue = 0.0;
            $totalItemsCount = 0;

            $consignment = Consignment::create([
                'consignment_number' => $number,
                'branch_id' => $branch->id,
                'type' => $validated['type'],
                'partner_name' => $validated['partner_name'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'start_date' => $validated['start_date'],
                'due_settlement_date' => $validated['due_settlement_date'] ?? null,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
                'total_items_count' => 0,
                'total_consigned_value' => 0.0,
                'total_sold_value' => 0.0,
                'total_settled_amount' => 0.0,
                'remaining_settlement_due' => 0.0,
            ]);

            foreach ($validated['items'] as $itemData) {
                $unitPrice = (float) $itemData['unit_price'];
                $qty = (int) $itemData['qty_consigned'];
                $subtotal = $unitPrice * $qty;

                ConsignmentItem::create([
                    'consignment_id' => $consignment->id,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'unit_price' => $unitPrice,
                    'qty_consigned' => $qty,
                    'qty_sold' => 0,
                    'qty_returned' => 0,
                    'qty_remaining' => $qty,
                    'subtotal_consigned' => $subtotal,
                    'subtotal_sold' => 0.0,
                ]);

                $totalConsignedValue += $subtotal;
                $totalItemsCount++;
            }

            $consignment->update([
                'total_items_count' => $totalItemsCount,
                'total_consigned_value' => $totalConsignedValue,
            ]);

            DB::commit();

            $msg = "Kontrak konsinyasi {$number} berhasil didaftarkan dengan {$totalItemsCount} SKU (Nilai Rp ".number_format($totalConsignedValue, 0, ',', '.').').';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'consignment' => $consignment->load('items.variant'),
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Consignment creation error: '.$e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal membuat kontrak konsinyasi: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal membuat kontrak konsinyasi: '.$e->getMessage());
        }
    }

    /**
     * Catat Rekonsiliasi Penjualan & Setoran Pembayaran Konsinyasi.
     */
    public function settleConsignment(Request $request, Consignment $consignment): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:consignment_items,id'],
            'items.*.qty_sold_now' => ['required', 'integer', 'min:0'],
            'settled_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            $newTotalSoldValue = (float) $consignment->total_sold_value;

            foreach ($validated['items'] as $itemData) {
                $item = ConsignmentItem::where('consignment_id', $consignment->id)->find($itemData['id']);
                if (! $item) {
                    continue;
                }

                $soldNow = (int) $itemData['qty_sold_now'];
                if ($soldNow <= 0) {
                    continue;
                }

                $item->qty_sold += $soldNow;
                $item->qty_remaining = max(0, $item->qty_consigned - $item->qty_sold - $item->qty_returned);
                $item->subtotal_sold = $item->qty_sold * $item->unit_price;
                $item->save();

                $newTotalSoldValue += ($soldNow * $item->unit_price);
            }

            $settledAmount = (float) $validated['settled_amount'];
            $newTotalSettled = (float) $consignment->total_settled_amount + $settledAmount;
            $remainingDue = max(0.0, $newTotalSoldValue - $newTotalSettled);

            $status = ($remainingDue <= 0.0 && $consignment->items()->sum('qty_remaining') <= 0) ? 'settled' : 'active';

            $consignment->update([
                'total_sold_value' => $newTotalSoldValue,
                'total_settled_amount' => $newTotalSettled,
                'remaining_settlement_due' => $remainingDue,
                'status' => $status,
                'notes' => $validated['notes'] ?? $consignment->notes,
            ]);

            // Bukukan Jurnal Umum SAK Konsinyasi Otomatis (General Ledger Double-Entry)
            $journal = $this->autoJournalService->journalConsignmentSettlement($consignment, $settledAmount, $validated['notes'] ?? null);

            DB::commit();

            $msg = "Rekonsiliasi konsinyasi {$consignment->consignment_number} berhasil disimpan. Setoran: Rp ".number_format($settledAmount, 0, ',', '.').', Sisa Hak Setor: Rp '.number_format($remainingDue, 0, ',', '.').'.';
            if ($journal) {
                $msg .= " Jurnal penyesuaian otomatis {$journal->entry_number} telah dibukukan ke General Ledger.";
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'consignment' => $consignment->fresh(['items.variant', 'journalEntries.lines.chartOfAccount']),
                    'journal' => $journal,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Consignment settlement error: '.$e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan setoran konsinyasi: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal menyimpan setoran konsinyasi: '.$e->getMessage());
        }
    }

    /**
     * Ekspor Laporan Aging Piutang ke CSV Excel.
     */
    public function exportAgingCsv(Request $request): StreamedResponse
    {
        $selectedBranchId = $request->input('branch_id', 'all');
        $selectedType = $request->input('customer_type', 'all');
        $asOfDate = $request->filled('as_of_date') ? Carbon::parse($request->input('as_of_date')) : Carbon::today();

        $agingInvoices = $this->getAgingInvoices($selectedBranchId, $selectedType, $asOfDate);
        $fileName = 'aging_piutang_grosir_'.$asOfDate->format('Ymd').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($agingInvoices, $asOfDate): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['=== LAPORAN AGING SCHEDULE PIUTANG GROSIR & B2B PT MAYA GRAHA KENCANA ===']);
            fputcsv($handle, ['Posisi Tanggal (As of Date):', $asOfDate->format('d/m/Y')]);
            fputcsv($handle, ['Waktu Ekspor:', Carbon::now()->format('d/m/Y H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'No.',
                'No. Faktur',
                'Titik Cabang / DC',
                'Nama Pelanggan',
                'Kategori Pelanggan',
                'Tanggal Faktur',
                'Jatuh Tempo',
                'Hari Terlewat (DPD)',
                'Klasifikasi Umur (Aging)',
                'Nilai Faktur (Rp)',
                'Sudah Dibayar (Rp)',
                'Sisa Piutang (Rp)',
                'Status Piutang',
            ]);

            $no = 1;
            foreach ($agingInvoices as $inv) {
                fputcsv($handle, [
                    $no++,
                    $inv['invoice_number'],
                    $inv['branch_name'],
                    $inv['customer_name'],
                    $inv['customer_type'],
                    $inv['invoice_date'],
                    $inv['due_date'],
                    $inv['days_overdue'],
                    $inv['aging_bucket'],
                    number_format($inv['total_amount'], 2, ',', '.'),
                    number_format($inv['paid_amount'], 2, ',', '.'),
                    number_format($inv['remaining_balance'], 2, ',', '.'),
                    $inv['status_label'],
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ekspor Laporan Saldo Konsinyasi ke CSV Excel.
     */
    public function exportConsignmentCsv(Request $request): StreamedResponse
    {
        $selectedBranchId = $request->input('branch_id', 'all');
        $query = Consignment::with(['branch', 'supplier', 'customer', 'items.variant'])->latest('id');

        if ($selectedBranchId !== 'all') {
            $query->where('branch_id', $selectedBranchId);
        }

        $consignments = $query->get();
        $fileName = 'saldo_konsinyasi_cabang_'.date('Ymd').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($consignments): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['=== LAPORAN MONITORING SALDO KONSINYASI MULTI-CABANG PT MAYA GRAHA KENCANA ===']);
            fputcsv($handle, ['Waktu Ekspor:', Carbon::now()->format('d/m/Y H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'No.',
                'No. Kontrak Konsinyasi',
                'Cabang',
                'Jenis Titipan',
                'Nama Mitra / Vendor',
                'Tanggal Mulai',
                'Target Setoran',
                'Status Kontrak',
                'Total Nilai Titipan (Rp)',
                'Total Laku Terjual (Rp)',
                'Sudah Disetor (Rp)',
                'Sisa Hak Setor (Rp)',
                'Catatan',
            ]);

            $no = 1;
            foreach ($consignments as $c) {
                fputcsv($handle, [
                    $no++,
                    $c->consignment_number,
                    $c->branch->name ?? '-',
                    $c->type === 'inward' ? 'TITIPAN SUPPLIER (INWARD)' : 'TITIPAN MITRA (OUTWARD)',
                    $c->partner_name,
                    $c->start_date ? $c->start_date->format('d/m/Y') : '-',
                    $c->due_settlement_date ? $c->due_settlement_date->format('d/m/Y') : '-',
                    strtoupper($c->status),
                    number_format($c->total_consigned_value, 2, ',', '.'),
                    number_format($c->total_sold_value, 2, ',', '.'),
                    number_format($c->total_settled_amount, 2, ',', '.'),
                    number_format($c->remaining_settlement_due, 2, ',', '.'),
                    $c->notes ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Hitung Aging Faktur Piutang dari SalesInvoice & CafeSale.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getAgingInvoices(string $branchId, string $customerType, Carbon $asOfDate): array
    {
        $invoices = [];

        // 1. Ambil dari SalesInvoice (Modul Penjualan B2B)
        $siQuery = SalesInvoice::with(['customer', 'branch'])
            ->where('payment_status', '!=', 'paid')
            ->where('remaining_balance', '>', 0);

        if ($branchId !== 'all') {
            $siQuery->where('branch_id', $branchId);
        }

        if ($customerType !== 'all') {
            $siQuery->whereHas('customer', function ($q) use ($customerType): void {
                $q->where('customer_type', $customerType);
            });
        }

        foreach ($siQuery->get() as $si) {
            $dueDate = $si->due_date ? Carbon::parse($si->due_date) : Carbon::parse($si->invoice_date)->addDays(30);
            $daysOverdue = (int) $asOfDate->diffInDays($dueDate, false);
            // diffInDays false: jika dueDate masa depan -> positif, jika masa lalu (terlambat) -> negatif

            $agingBucket = 'Current (Belum Jatuh Tempo)';
            $dpd = 0;

            if ($asOfDate->greaterThan($dueDate)) {
                $dpd = (int) $dueDate->diffInDays($asOfDate);
                if ($dpd <= 30) {
                    $agingBucket = '1 - 30 Hari';
                } elseif ($dpd <= 60) {
                    $agingBucket = '31 - 60 Hari';
                } elseif ($dpd <= 90) {
                    $agingBucket = '61 - 90 Hari';
                } else {
                    $agingBucket = '> 90 Hari (Macet)';
                }
            }

            $typeLabel = $si->customer?->customer_type instanceof \BackedEnum
                ? $si->customer->customer_type->value
                : (string) ($si->customer?->customer_type ?? 'Grosir');

            $invoices[] = [
                'source' => 'SalesInvoice',
                'id' => $si->id,
                'invoice_number' => $si->invoice_number,
                'branch_id' => $si->branch_id,
                'branch_name' => $si->branch->name ?? 'Cabang Pusat',
                'customer_id' => $si->customer_id,
                'customer_name' => $si->customer->name ?? 'Pelanggan Umum',
                'customer_type' => strtoupper($typeLabel),
                'invoice_date' => $si->invoice_date ? Carbon::parse($si->invoice_date)->format('d/m/Y') : '-',
                'due_date' => $dueDate->format('d/m/Y'),
                'days_overdue' => $dpd,
                'aging_bucket' => $agingBucket,
                'total_amount' => (float) $si->net_amount,
                'paid_amount' => (float) $si->paid_amount,
                'remaining_balance' => (float) $si->remaining_balance,
                'status_label' => $dpd > 0 ? "Jatuh Tempo ({$dpd} Hari)" : 'Lancar',
            ];
        }

        // 2. Ambil dari CafeSale yang belum lunas (Kredit kasir POS)
        $csQuery = CafeSale::with(['customer', 'branch'])
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('customer_id');

        if ($branchId !== 'all') {
            $csQuery->where('branch_id', $branchId);
        }

        if ($customerType !== 'all') {
            $csQuery->whereHas('customer', function ($q) use ($customerType): void {
                $q->where('customer_type', $customerType);
            });
        }

        foreach ($csQuery->get() as $cs) {
            $net = (float) $cs->net_amount;
            $paid = (float) $cs->amount_paid;
            $remaining = max(0.0, $net - $paid);
            if ($remaining <= 0) {
                continue;
            }

            $termDays = (int) ($cs->customer->payment_term_days ?? 14);
            $invDate = Carbon::parse($cs->created_at);
            $dueDate = $invDate->copy()->addDays($termDays);

            $dpd = 0;
            $agingBucket = 'Current (Belum Jatuh Tempo)';

            if ($asOfDate->greaterThan($dueDate)) {
                $dpd = (int) $dueDate->diffInDays($asOfDate);
                if ($dpd <= 30) {
                    $agingBucket = '1 - 30 Hari';
                } elseif ($dpd <= 60) {
                    $agingBucket = '31 - 60 Hari';
                } elseif ($dpd <= 90) {
                    $agingBucket = '61 - 90 Hari';
                } else {
                    $agingBucket = '> 90 Hari (Macet)';
                }
            }

            $typeLabel = $cs->customer?->customer_type instanceof \BackedEnum
                ? $cs->customer->customer_type->value
                : (string) ($cs->customer?->customer_type ?? 'Retail');

            $invoices[] = [
                'source' => 'CafeSale',
                'id' => $cs->id,
                'invoice_number' => $cs->invoice_number,
                'branch_id' => $cs->branch_id,
                'branch_name' => $cs->branch->name ?? 'Outlet',
                'customer_id' => $cs->customer_id,
                'customer_name' => $cs->customer->name ?? $cs->customer_name,
                'customer_type' => strtoupper($typeLabel),
                'invoice_date' => $invDate->format('d/m/Y'),
                'due_date' => $dueDate->format('d/m/Y'),
                'days_overdue' => $dpd,
                'aging_bucket' => $agingBucket,
                'total_amount' => $net,
                'paid_amount' => $paid,
                'remaining_balance' => $remaining,
                'status_label' => $dpd > 0 ? "Jatuh Tempo ({$dpd} Hari)" : 'Lancar',
            ];
        }

        // Urutkan dari yang paling lama jatuh tempo
        usort($invoices, function ($a, $b) {
            return $b['days_overdue'] <=> $a['days_overdue'];
        });

        return $invoices;
    }

    /**
     * Agregasi Piutang per Pelanggan ke dalam Bucket Kolom SAK.
     *
     * @param  array<int, array<string, mixed>>  $invoices
     * @return array<int, array<string, mixed>>
     */
    private function aggregateCustomerAging(array $invoices): array
    {
        $grouped = [];

        foreach ($invoices as $inv) {
            $cId = $inv['customer_id'] ?? 0;
            if (! isset($grouped[$cId])) {
                $grouped[$cId] = [
                    'customer_id' => $cId,
                    'customer_name' => $inv['customer_name'],
                    'customer_type' => $inv['customer_type'],
                    'branch_name' => $inv['branch_name'],
                    'total_outstanding' => 0.0,
                    'current' => 0.0,
                    'days_1_30' => 0.0,
                    'days_31_60' => 0.0,
                    'days_61_90' => 0.0,
                    'days_over_90' => 0.0,
                    'invoices_count' => 0,
                ];
            }

            $rem = (float) $inv['remaining_balance'];
            $grouped[$cId]['total_outstanding'] += $rem;
            $grouped[$cId]['invoices_count']++;

            $bucket = $inv['aging_bucket'];
            if (str_starts_with($bucket, 'Current')) {
                $grouped[$cId]['current'] += $rem;
            } elseif ($bucket === '1 - 30 Hari') {
                $grouped[$cId]['days_1_30'] += $rem;
            } elseif ($bucket === '31 - 60 Hari') {
                $grouped[$cId]['days_31_60'] += $rem;
            } elseif ($bucket === '61 - 90 Hari') {
                $grouped[$cId]['days_61_90'] += $rem;
            } else {
                $grouped[$cId]['days_over_90'] += $rem;
            }
        }

        // Urutkan konsumen dengan total piutang terbesar
        usort($grouped, function ($a, $b) {
            return $b['total_outstanding'] <=> $a['total_outstanding'];
        });

        return array_values($grouped);
    }
}
