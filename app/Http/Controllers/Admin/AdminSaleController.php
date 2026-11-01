<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Attendance\Models\AuditLog;
use App\Http\Controllers\Controller;
use App\Models\ShiftSalesSummary;
use App\Models\ShiftStockRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafePayment;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\PcStation;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Services\CafeCheckoutService;

/**
 * Controller Admin ERP Maya Graha Kencana: Manajemen Transaksi Penjualan & Kasir POS.
 */
class AdminSaleController extends Controller
{
    public function __construct(
        private readonly CafeCheckoutService $checkoutService
    ) {}

    /**
     * Tampilkan halaman Daftar Transaksi Penjualan ERP MGK.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CafeSale::with(['employee', 'pcStation', 'branch', 'items.variant.product', 'payments', 'customer'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like): void {
                $q->where('invoice_number', $like, "%{$search}%")
                    ->orWhere('customer_name', $like, "%{$search}%")
                    ->orWhere('payment_reference', $like, "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search, $like): void {
                        $eq->where('name', $like, "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($cq) use ($search, $like): void {
                        $cq->where('name', $like, "%{$search}%")
                            ->orWhere('code', $like, "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search, $like): void {
                        $iq->where('item_name', $like, "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('payment_status', $request->input('status'));
        }

        if ($request->filled('payment_method') && $request->input('payment_method') !== 'all') {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('employee_id') && $request->input('employee_id') !== 'all') {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }

        if ($request->filled('pc_station_id') && $request->input('pc_station_id') !== 'all') {
            $query->where('pc_station_id', (int) $request->input('pc_station_id'));
        }

        if ($request->filled('branch_id') && $request->input('branch_id') !== 'all') {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($request->filled('date')) {
            $dateFilter = $request->input('date');
            if ($dateFilter === 'today') {
                $query->whereDate('created_at', now()->toDateString());
            } elseif ($dateFilter === 'this_week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($dateFilter === 'this_month') {
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            }
        }

        $sales = $query->get();

        // Metrik Ringkasan Transaksi
        $metrics = [
            'total_sales' => CafeSale::where('payment_status', 'paid')->sum('net_amount'),
            'total_billing' => CafeSale::where('payment_status', 'paid')->sum('total_billing'),
            'total_fnb' => CafeSale::where('payment_status', 'paid')->sum('total_fnb'),
            'today_sales' => CafeSale::whereDate('created_at', now()->toDateString())
                ->where('payment_status', 'paid')
                ->sum('net_amount'),
            'total_count' => CafeSale::count(),
            'paid_count' => CafeSale::where('payment_status', 'paid')->count(),
            'pending_count' => CafeSale::where('payment_status', 'pending')->count(),
            'cancelled_count' => CafeSale::where('payment_status', 'cancelled')->count(),
        ];

        $branches = Branch::orderBy('name', 'asc')->get();
        $employees = Employee::orderBy('name', 'asc')->get();
        $pcStations = PcStation::orderBy('station_code', 'asc')->get();
        $variants = ProductVariant::with('product')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // Data Monitoring Rekap Tutup Shift & Audit Selisih Kas (Blind Cash Count)
        $shiftSummaries = ShiftSalesSummary::with(['employee', 'branch', 'shift'])
            ->when($request->filled('branch_id') && $request->input('branch_id') !== 'all', fn ($q) => $q->where('branch_id', (int) $request->input('branch_id')))
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        // Data Permintaan Stok Barang dari Kasir Shift
        $stockRequests = ShiftStockRequest::with(['employee', 'branch', 'shift'])
            ->when($request->filled('branch_id') && $request->input('branch_id') !== 'all', fn ($q) => $q->where('branch_id', (int) $request->input('branch_id')))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $sales->count(),
                'metrics' => $metrics,
                'sales' => $sales,
                'shift_summaries' => $shiftSummaries,
                'stock_requests' => $stockRequests,
            ]);
        }

        return view('admin.penjualan.index', compact(
            'sales',
            'metrics',
            'branches',
            'employees',
            'pcStations',
            'variants',
            'shiftSummaries',
            'stockRequests'
        ));
    }

    /**
     * Tampilkan detail transaksi penjualan.
     */
    public function show(CafeSale $sale): JsonResponse|View
    {
        $sale->load(['employee', 'pcStation', 'branch', 'items.variant.product', 'payments']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'sale' => $sale,
            ]);
        }

        return view('admin.penjualan.show', compact('sale'));
    }

    /**
     * Simpan transaksi penjualan baru melalui Domain Service.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'pc_station_id' => ['nullable', 'exists:pc_stations,id'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,debit'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:150'],
            'cashier_notes' => ['nullable', 'string', 'max:500'],
            'finish_pc_session' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.item_type' => ['required', 'string', 'in:billing,fnb,voucher,makanan,minuman,snack'],
            'items.*.name' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $sale = $this->checkoutService->execute($validated);
        $sale->load(['items', 'payments', 'employee', 'pcStation']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi penjualan {$sale->invoice_number} berhasil dibuat.",
                'sale' => $sale,
            ], 201);
        }

        return redirect()->route('admin.penjualan')->with('success', "Transaksi {$sale->invoice_number} berhasil dicatat.");
    }

    /**
     * Update data / status transaksi penjualan.
     */
    public function update(Request $request, CafeSale $sale): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['sometimes', 'string', 'max:150'],
            'payment_status' => ['sometimes', 'string', 'in:paid,pending,cancelled'],
            'payment_method' => ['sometimes', 'string', 'in:cash,qris,transfer,debit'],
            'cashier_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $sale->update($validated);

        // Sinkronisasi status payment jika diubah ke cancelled
        if (isset($validated['payment_status'])) {
            $paymentStatus = match ($validated['payment_status']) {
                'paid' => 'completed',
                'cancelled' => 'refunded',
                default => 'pending',
            };
            CafePayment::where('cafe_sale_id', $sale->id)->update(['status' => $paymentStatus]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi {$sale->invoice_number} berhasil diperbarui.",
                'sale' => $sale->fresh(['items', 'payments', 'employee', 'pcStation']),
            ]);
        }

        return redirect()->route('admin.penjualan')->with('success', "Transaksi {$sale->invoice_number} berhasil diperbarui.");
    }

    /**
     * Update status cepat (Lunas / Batal / Pending).
     */
    public function updateStatus(Request $request, CafeSale $sale): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:paid,pending,cancelled'],
        ]);

        $sale->update(['payment_status' => $validated['status']]);

        $paymentStatus = match ($validated['status']) {
            'paid' => 'completed',
            'cancelled' => 'refunded',
            default => 'pending',
        };
        CafePayment::where('cafe_sale_id', $sale->id)->update(['status' => $paymentStatus]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status {$sale->invoice_number} diubah menjadi {$validated['status']}.",
                'payment_status' => $sale->payment_status,
            ]);
        }

        return redirect()->route('admin.penjualan');
    }

    /**
     * Hapus transaksi penjualan dari sistem.
     */
    public function destroy(CafeSale $sale): JsonResponse|RedirectResponse
    {
        $invoice = $sale->invoice_number;
        $sale->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi {$invoice} berhasil dihapus dari sistem.",
            ]);
        }

        return redirect()->route('admin.penjualan')->with('success', "Transaksi {$invoice} berhasil dihapus.");
    }

    /**
     * Tampilkan struk / invoice cetak kasir.
     */
    public function printReceipt(CafeSale $sale): View
    {
        $sale->load(['employee', 'pcStation', 'branch', 'items.variant.product', 'payments']);

        return view('admin.penjualan.print', compact('sale'));
    }

    /**
     * Setujui / Penuhi atau Tolak Permintaan Stok Barang dari Kasir Shift.
     */
    public function updateStockRequestStatus(Request $request, ShiftStockRequest $stockRequest): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:fulfilled,approved,rejected,pending'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $validated['status'] === 'approved' ? 'fulfilled' : $validated['status'];

        $stockRequest->status = $newStatus;
        if ($newStatus === 'fulfilled') {
            $stockRequest->fulfilled_at = now();
        }
        if (! empty($validated['notes'])) {
            $stockRequest->notes = trim(($stockRequest->notes ? $stockRequest->notes.' | ' : '').'Admin: '.$validated['notes']);
        }
        $stockRequest->save();

        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => ShiftStockRequest::class,
            'auditable_id' => $stockRequest->id,
            'action' => 'shift_stock_request_'.$newStatus,
            'new_values' => [
                'request_number' => $stockRequest->request_number,
                'item_name' => $stockRequest->item_name,
                'qty_requested' => $stockRequest->qty_requested,
                'status' => $newStatus,
                'fulfilled_at' => $stockRequest->fulfilled_at?->toDateTimeString(),
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Admin ERP MGK',
            'created_at' => now(),
        ]));

        $statusLabel = $newStatus === 'fulfilled' ? 'Disetujui / Dipenuhi' : ($newStatus === 'rejected' ? 'Ditolak' : 'Diperbarui');
        $message = "Permintaan stok {$stockRequest->request_number} ({$stockRequest->item_name}) berhasil {$statusLabel}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'stock_request' => $stockRequest,
            ]);
        }

        return redirect()->route('admin.penjualan')->with('success', $message);
    }

    /**
     * Antrean persetujuan Penyesuaian Billing & Print Kertas kasir.
     */
    public function billingAdjustmentIndex(Request $request): View
    {
        $assignedBranchId = $this->assignedBranchId();

        $baseQuery = ShiftSalesSummary::with(['employee', 'branch', 'shift'])
            ->when($assignedBranchId, fn ($q) => $q->where('branch_id', $assignedBranchId));

        $pendingAdjustments = (clone $baseQuery)
            ->where('billing_adjustment_status', 'pending_approval')
            ->orderByDesc('id')
            ->get();

        $processedAdjustments = (clone $baseQuery)
            ->whereIn('billing_adjustment_status', ['approved', 'rejected'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $pendingPrintAdjustments = (clone $baseQuery)
            ->where('print_adjustment_status', 'pending_approval')
            ->orderByDesc('id')
            ->get();

        $processedPrintAdjustments = (clone $baseQuery)
            ->whereIn('print_adjustment_status', ['approved', 'rejected'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $profileData = AdminDashboardController::getProfileData($request);

        return view('admin.penjualan.penyesuaian-billing', compact(
            'pendingAdjustments',
            'processedAdjustments',
            'pendingPrintAdjustments',
            'processedPrintAdjustments',
            'profileData'
        ));
    }

    /**
     * Setujui pengajuan penyesuaian billing: potongan resmi memotong omset kasir.
     */
    public function approveBillingAdjustment(Request $request, ShiftSalesSummary $summary): JsonResponse|RedirectResponse
    {
        if ($blocked = $this->guardBillingAdjustment($request, $summary)) {
            return $blocked;
        }

        $adminName = auth()->user()?->name ?? 'Admin';
        $rawAmount = (float) ($summary->raw_billing_amount ?? $summary->locked_billing_amount ?? 0);
        $summary->approveBillingAdjustment($adminName);

        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_billing_adjustment_approved_by_admin',
            'old_values' => ['locked_billing_amount' => $rawAmount],
            'new_values' => [
                'status' => 'approved',
                'approved_by' => $adminName,
                'locked_billing_amount' => (float) $summary->locked_billing_amount,
                'adjustment_amount' => (float) $summary->billing_adjustment_amount,
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Admin ERP MGK',
            'created_at' => now(),
        ]));

        $message = 'Penyesuaian billing kasir '.($summary->employee?->name ?? '-').' sebesar Rp '.number_format((float) $summary->billing_adjustment_amount, 0, ',', '.')." DISETUJUI oleh {$adminName}.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'summary' => $summary->fresh()]);
        }

        return redirect()->route('admin.penjualan.penyesuaian-billing')->with('success', $message);
    }

    /**
     * Tolak pengajuan penyesuaian billing: kasir wajib menyetor omset penuh.
     */
    public function rejectBillingAdjustment(Request $request, ShiftSalesSummary $summary): JsonResponse|RedirectResponse
    {
        if ($blocked = $this->guardBillingAdjustment($request, $summary)) {
            return $blocked;
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $adminName = auth()->user()?->name ?? 'Admin';
        $summary->rejectBillingAdjustment($adminName, $validated['rejection_reason']);

        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_billing_adjustment_rejected_by_admin',
            'old_values' => ['billing_adjustment_amount' => (float) $summary->billing_adjustment_amount],
            'new_values' => [
                'status' => 'rejected',
                'rejected_by' => $adminName,
                'rejection_reason' => $validated['rejection_reason'],
                'locked_billing_amount' => (float) $summary->locked_billing_amount,
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Admin ERP MGK',
            'created_at' => now(),
        ]));

        $message = 'Penyesuaian billing kasir '.($summary->employee?->name ?? '-')." DITOLAK oleh {$adminName}. Kasir wajib menyetor omset penuh.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'summary' => $summary->fresh()]);
        }

        return redirect()->route('admin.penjualan.penyesuaian-billing')->with('success', $message);
    }

    /**
     * Setujui pengajuan penyesuaian print kertas: potongan lembar/rupiah memotong target print kasir.
     */
    public function approvePrintAdjustment(Request $request, ShiftSalesSummary $summary): JsonResponse|RedirectResponse
    {
        if ($blocked = $this->guardPrintAdjustment($request, $summary)) {
            return $blocked;
        }

        $adminName = auth()->user()?->name ?? 'Admin';
        $rawAmount = (float) ($summary->raw_print_amount ?? $summary->locked_print_amount ?? 0);
        $rawPages = (int) ($summary->raw_print_pages ?? $summary->locked_print_pages ?? 0);
        $adjAmount = (float) ($summary->print_adjustment_amount ?? 0);
        $adjPages = (int) ($summary->print_adjustment_pages ?? 0);

        $summary->approvePrintAdjustment($adminName);

        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_print_adjustment_approved_by_admin',
            'old_values' => ['locked_print_amount' => $rawAmount, 'locked_print_pages' => $rawPages],
            'new_values' => [
                'status' => 'approved',
                'approved_by' => $adminName,
                'potongan_pages' => $adjPages,
                'potongan_amount' => $adjAmount,
                'net_print_amount' => (float) $summary->locked_print_amount,
                'net_print_pages' => (int) $summary->locked_print_pages,
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Admin ERP MGK',
            'created_at' => now(),
        ]));

        $message = 'Penyesuaian print kertas kasir '.($summary->employee?->name ?? '-')." ({$adjPages} lbr / Rp ".number_format($adjAmount, 0, ',', '.').") DISETUJUI oleh {$adminName}.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'summary' => $summary->fresh()]);
        }

        return redirect()->route('admin.penjualan.penyesuaian-billing')->with('success', $message);
    }

    /**
     * Tolak pengajuan penyesuaian print kertas: kasir wajib menyetor omset penuh.
     */
    public function rejectPrintAdjustment(Request $request, ShiftSalesSummary $summary): JsonResponse|RedirectResponse
    {
        if ($blocked = $this->guardPrintAdjustment($request, $summary)) {
            return $blocked;
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $adminName = auth()->user()?->name ?? 'Admin';
        $rawAmount = (float) ($summary->raw_print_amount ?? $summary->locked_print_amount ?? 0);
        $rawPages = (int) ($summary->raw_print_pages ?? $summary->locked_print_pages ?? 0);

        $summary->rejectPrintAdjustment($adminName, $validated['rejection_reason']);

        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_print_adjustment_rejected_by_admin',
            'old_values' => ['print_adjustment_amount' => (float) $summary->print_adjustment_amount],
            'new_values' => [
                'status' => 'rejected',
                'rejected_by' => $adminName,
                'rejection_reason' => $validated['rejection_reason'],
                'locked_print_amount' => $rawAmount,
                'locked_print_pages' => $rawPages,
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Admin ERP MGK',
            'created_at' => now(),
        ]));

        $message = 'Penyesuaian print kertas kasir '.($summary->employee?->name ?? '-')." DITOLAK oleh {$adminName}. Kasir wajib menyetor omset penuh {$rawPages} lembar (Rp ".number_format($rawAmount, 0, ',', '.').').';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'summary' => $summary->fresh()]);
        }

        return redirect()->route('admin.penjualan.penyesuaian-billing')->with('success', $message);
    }

    /**
     * Cabang yang dibatasi untuk admin cabang (null = semua cabang).
     */
    private function assignedBranchId(): ?int
    {
        $adminUser = auth()->user();
        $isBranchAdmin = $adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin();

        return $isBranchAdmin ? (int) $adminUser->assigned_branch_id : null;
    }

    /**
     * Pastikan pengajuan billing berstatus pending dan berada dalam cakupan cabang admin.
     */
    private function guardBillingAdjustment(Request $request, ShiftSalesSummary $summary): JsonResponse|RedirectResponse|null
    {
        $assignedBranchId = $this->assignedBranchId();
        $message = null;
        $status = 422;

        if ($assignedBranchId && (int) $summary->branch_id !== $assignedBranchId) {
            $message = 'Anda tidak berwenang memproses pengajuan cabang lain.';
            $status = 403;
        } elseif (! $summary->isBillingAdjustmentPending()) {
            $message = 'Pengajuan ini sudah diproses atau tidak dalam status menunggu persetujuan.';
        }

        if ($message === null) {
            return null;
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        return redirect()->route('admin.penjualan.penyesuaian-billing')->with('error', $message);
    }

    /**
     * Pastikan pengajuan print berstatus pending dan berada dalam cakupan cabang admin.
     */
    private function guardPrintAdjustment(Request $request, ShiftSalesSummary $summary): JsonResponse|RedirectResponse|null
    {
        $assignedBranchId = $this->assignedBranchId();
        $message = null;
        $status = 422;

        if ($assignedBranchId && (int) $summary->branch_id !== $assignedBranchId) {
            $message = 'Anda tidak berwenang memproses pengajuan cabang lain.';
            $status = 403;
        } elseif (! $summary->isPrintAdjustmentPending()) {
            $message = 'Pengajuan penyesuaian print ini sudah diproses atau tidak dalam status menunggu persetujuan.';
        }

        if ($message === null) {
            return null;
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        return redirect()->route('admin.penjualan.penyesuaian-billing')->with('error', $message);
    }

    /**
     * Verifikasi / Terima Setoran Kas Shift dari Kasir (History Setor).
     */
    public function verifyShiftDeposit(Request $request, ShiftSalesSummary $summary): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'deposit_status' => ['required', 'string', 'in:verified,rejected'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $adminName = auth()->user()?->name ?? 'Admin Keuangan';
        $status = $validated['deposit_status'];

        $summary->deposit_status = $status;
        $summary->deposit_verified_at = now();
        $summary->deposit_verified_by = $adminName;
        if (! empty($validated['notes'])) {
            $summary->deposit_notes = trim(($summary->deposit_notes ? $summary->deposit_notes.' | ' : '').'Verifikasi Admin: '.$validated['notes']);
        }
        if ($summary->deposited_amount === null) {
            $summary->deposited_amount = $summary->blind_cash_count ?? $summary->cash_in_drawer;
            $summary->deposited_at = now();
        }
        $summary->save();

        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_deposit_'.$status,
            'new_values' => [
                'summary_id' => $summary->id,
                'employee_id' => $summary->employee_id,
                'deposited_amount' => $summary->deposited_amount,
                'deposit_status' => $status,
                'verified_by' => $adminName,
                'verified_at' => $summary->deposit_verified_at?->toDateTimeString(),
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Admin ERP MGK',
            'created_at' => now(),
        ]));

        $label = $status === 'verified' ? 'Diverifikasi & Diterima' : 'Ditolak / Perlu Revisi';
        $message = "Setoran kas shift #{$summary->id} berhasil {$label} oleh {$adminName}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'summary' => $summary->fresh(),
            ]);
        }

        return redirect()->route('admin.penjualan')->with('success', $message);
    }
}
