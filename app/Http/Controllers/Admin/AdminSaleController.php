<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
        $query = CafeSale::with(['employee', 'pcStation', 'branch', 'items.variant.product', 'payments'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('invoice_number', 'ilike', "%{$search}%")
                    ->orWhere('customer_name', 'ilike', "%{$search}%")
                    ->orWhere('payment_reference', 'ilike', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search): void {
                        $eq->where('name', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search): void {
                        $iq->where('item_name', 'ilike', "%{$search}%");
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

        $employees = Employee::orderBy('name', 'asc')->get();
        $pcStations = PcStation::orderBy('station_code', 'asc')->get();
        $variants = ProductVariant::with('product')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $sales->count(),
                'metrics' => $metrics,
                'sales' => $sales,
            ]);
        }

        return view('admin.penjualan.index', compact(
            'sales',
            'metrics',
            'employees',
            'pcStations',
            'variants'
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
}
