<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftStockRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\ProductPricing;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;

/**
 * Controller Pengelolaan Master Data Management (MDM) ERP Multi-Cabang Hub-and-Spoke.
 * Mengelola:
 * 1. Master Cabang (HQ, DC, Store)
 * 2. Lokalisasi Produk Cabang (Branch Products: Bin Location, Min/Max, ROP)
 * 3. Zonasi Harga Jual & HPP Multi-Cabang (Product Pricings)
 */
class AdminBranchMdmController extends Controller
{
    /**
     * 1. Halaman Daftar Cabang & Jaringan Distribusi.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Branch::with(['warehouses', 'employees'])
            ->withCount(['branchProducts', 'employees'])
            ->orderBy('id', 'asc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }

        $branches = $query->get();

        $stats = [
            'total' => Branch::count(),
            'hq' => Branch::where('type', BranchType::Hq)->orWhere('is_headquarters', true)->count(),
            'dc' => Branch::where('type', BranchType::Dc)->count(),
            'store' => Branch::where('type', BranchType::Store)->where('is_headquarters', false)->count(),
            'active' => Branch::where('is_active', true)->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'branches' => $branches,
                'stats' => $stats,
            ]);
        }

        return view('admin.cabang.index', compact('branches', 'stats'));
    }

    /**
     * Simpan cabang / distribution center baru.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::enum(BranchType::class)],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'radius_meters' => ['nullable', 'integer', 'min:10'],
            'wifi_ssid' => ['nullable', 'string', 'max:100'],
            'wifi_bssid' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['is_headquarters'] = ($validated['type'] === BranchType::Hq->value);
        $validated['is_active'] = true;

        $branch = Branch::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Cabang {$branch->name} berhasil didaftarkan.",
                'branch' => $branch,
            ]);
        }

        return redirect()->route('admin.cabang')->with('success', "Cabang {$branch->name} ({$branch->type->label()}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data cabang / distribution center.
     */
    public function update(Request $request, Branch $branch): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::enum(BranchType::class)],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'radius_meters' => ['nullable', 'integer', 'min:10'],
            'wifi_ssid' => ['nullable', 'string', 'max:100'],
            'wifi_bssid' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_headquarters'] = ($validated['type'] === BranchType::Hq->value);
        $branch->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data cabang {$branch->name} berhasil diperbarui.",
                'branch' => $branch,
            ]);
        }

        return redirect()->route('admin.cabang')->with('success', "Data cabang {$branch->name} berhasil diperbarui.");
    }

    /**
     * Hapus cabang jika belum memiliki relasi kritis.
     */
    public function destroy(Branch $branch): RedirectResponse|JsonResponse
    {
        $name = $branch->name;
        $branch->delete();

        return redirect()->route('admin.cabang')->with('success', "Cabang {$name} berhasil dihapus.");
    }

    /**
     * 2. Halaman Lokalisasi Produk Cabang (Branch Products: The Spoke).
     * Mengatur penempatan rak (bin_location), min/max stok, dan titik Reorder Point (ROP).
     */
    public function productsIndex(Request $request): View|JsonResponse
    {
        $branches = Branch::where('is_active', true)->orderBy('id')->get();
        $selectedBranchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : ($branches->first()?->id ?? 1);
        $selectedBranch = Branch::find($selectedBranchId);

        $query = BranchProduct::with(['variant.product.category', 'branch'])
            ->where('branch_id', $selectedBranchId);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->whereHas('variant', function ($vq) use ($search): void {
                $vq->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhere('barcode', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('is_locally_active', $request->input('status') === 'active');
        }

        $branchProducts = $query->paginate(30)->withQueryString();

        // Cari warehouse milik cabang ini untuk membaca stok aktual
        $warehouseIds = $selectedBranch ? $selectedBranch->warehouses()->pluck('id')->toArray() : [];

        // Ambil mapping stok aktual on_hand dan reserved per varian
        $stockMap = [];
        if (! empty($warehouseIds)) {
            $inventories = StockInventory::whereIn('warehouse_id', $warehouseIds)->get();
            foreach ($inventories as $inv) {
                $stockMap[$inv->product_variant_id] = [
                    'qty_on_hand' => (int) $inv->qty_on_hand,
                    'qty_reserved' => (int) $inv->qty_reserved,
                    'qty_available' => (int) $inv->qty_available,
                ];
            }
        }

        // Hitung total produk butuh reorder (ROP Alert)
        $ropAlertCount = 0;
        foreach ($branchProducts as $bp) {
            $curr = $stockMap[$bp->product_variant_id]['qty_available'] ?? 0;
            if ($bp->needsReorder((float) $curr)) {
                $ropAlertCount++;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'branch' => $selectedBranch,
                'items' => $branchProducts,
                'stock_map' => $stockMap,
                'rop_alert_count' => $ropAlertCount,
            ]);
        }

        return view('admin.cabang.produk', compact(
            'branches',
            'selectedBranchId',
            'selectedBranch',
            'branchProducts',
            'stockMap',
            'ropAlertCount'
        ));
    }

    /**
     * Perbarui konfigurasi lokalisasi satu produk di cabang.
     */
    public function productUpdate(Request $request, BranchProduct $branchProduct): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'bin_location' => ['nullable', 'string', 'max:50'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'max_stock' => ['required', 'numeric', 'min:0'],
            'reorder_point' => ['required', 'numeric', 'min:0'],
            'is_locally_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_locally_active'] = $request->boolean('is_locally_active', true);
        $branchProduct->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Parameter lokalisasi produk berhasil diperbarui.',
                'branch_product' => $branchProduct,
            ]);
        }

        return back()->with('success', 'Lokalisasi rak dan ROP produk berhasil diperbarui.');
    }

    /**
     * Sinkronisasi otomatis: Tambahkan seluruh varian produk nasional ke cabang ini.
     */
    public function syncNationalProducts(Branch $branch): RedirectResponse
    {
        $variants = ProductVariant::where('is_active', true)->get();
        $syncedCount = 0;

        foreach ($variants as $variant) {
            $exists = BranchProduct::where('branch_id', $branch->id)
                ->where('product_variant_id', $variant->id)
                ->exists();

            if (! $exists) {
                BranchProduct::create([
                    'branch_id' => $branch->id,
                    'product_variant_id' => $variant->id,
                    'bin_location' => 'Rak-Utama',
                    'min_stock' => 5,
                    'max_stock' => 100,
                    'reorder_point' => 10,
                    'is_locally_active' => true,
                ]);
                $syncedCount++;
            }
        }

        return back()->with('success', "Sinkronisasi selesai. Sebanyak {$syncedCount} produk baru dialokasikan ke cabang {$branch->name}.");
    }

    /**
     * 3. Halaman Zonasi & Penetapan Harga Multi-Cabang (Product Pricings).
     */
    public function pricingsIndex(Request $request): View|JsonResponse
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $variants = ProductVariant::where('is_active', true)->with('product')->orderBy('name')->get();

        $query = ProductPricing::with(['variant.product', 'branch'])
            ->orderByDesc('id');

        if ($request->filled('branch_id') && $request->input('branch_id') !== 'all') {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($request->filled('zone_code') && $request->input('zone_code') !== 'all') {
            $query->where('zone_code', $request->input('zone_code'));
        }

        $pricings = $query->paginate(25)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'pricings' => $pricings,
            ]);
        }

        return view('admin.cabang.pricings', compact('pricings', 'branches', 'variants'));
    }

    /**
     * Simpan penetapan harga zonasi / cabang baru.
     */
    public function pricingStore(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'zone_code' => ['required', 'string', 'max:30'],
            'base_cost' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $pricing = ProductPricing::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kebijakan harga zonasi berhasil disimpan.',
                'pricing' => $pricing,
            ]);
        }

        return back()->with('success', 'Kebijakan harga zonasi produk berhasil ditetapkan.');
    }

    /**
     * 4. Dashboard Konsolidasi & Monitoring Multi-Cabang (Hub-and-Spoke Overview).
     */
    public function konsolidasi(Request $request): View|JsonResponse
    {
        $dateParam = $request->input('date');
        $filterDate = $dateParam ? Carbon::parse($dateParam) : Carbon::today();
        $monthStart = $filterDate->copy()->startOfMonth();
        $monthEnd = $filterDate->copy()->endOfMonth();

        // 1. Ambil seluruh data cabang beserta gudang & karyawan
        $branches = Branch::with(['warehouses', 'employees'])
            ->withCount(['employees', 'branchProducts'])
            ->orderBy('id', 'asc')
            ->get();

        // 2. Ambil estimasi HPP variant produk (cost_price)
        $variantCostPrices = ProductVariant::pluck('cost_price', 'id')->toArray();

        // Ambil pricing khusus cabang jika ada
        $branchPricings = ProductPricing::where('start_date', '<=', $filterDate->toDateString())
            ->where(function ($q) use ($filterDate): void {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $filterDate->toDateString());
            })
            ->get()
            ->groupBy(fn ($p) => ($p->branch_id ?? 'global').'-'.$p->product_variant_id);

        $branchMatrix = [];
        $totalValuation = 0.0;
        $dcValuation = 0.0;
        $storeValuation = 0.0;
        $networkTodaySales = 0.0;
        $networkMonthSales = 0.0;
        $networkCashierCount = 0;

        foreach ($branches as $branch) {
            $whIds = $branch->warehouses->pluck('id')->toArray();

            // Total unit stok & valuasi persediaan cabang
            $inventories = StockInventory::whereIn('warehouse_id', $whIds)->get();
            $branchStockQty = (int) $inventories->sum('qty_on_hand');

            $branchStockValuation = 0.0;
            foreach ($inventories as $inv) {
                // Cek harga HPP zonasi cabang, jika tidak ada fallback ke variant cost_price
                $pricingKey = $branch->id.'-'.$inv->product_variant_id;
                $globalPricingKey = 'global-'.$inv->product_variant_id;

                $effectiveCost = 0.0;
                if (isset($branchPricings[$pricingKey])) {
                    $effectiveCost = (float) $branchPricings[$pricingKey]->first()->base_cost;
                } elseif (isset($branchPricings[$globalPricingKey])) {
                    $effectiveCost = (float) $branchPricings[$globalPricingKey]->first()->base_cost;
                } else {
                    $effectiveCost = (float) ($variantCostPrices[$inv->product_variant_id] ?? 0.0);
                }

                $branchStockValuation += ($effectiveCost * (int) $inv->qty_on_hand);
            }

            $totalValuation += $branchStockValuation;
            if ($branch->isDc()) {
                $dcValuation += $branchStockValuation;
            } else {
                $storeValuation += $branchStockValuation;
            }

            // Penjualan Cabang Hari Ini
            $todaySalesQuery = CafeSale::where('branch_id', $branch->id)
                ->whereDate('created_at', $filterDate->toDateString());
            $todaySalesAmount = (float) (clone $todaySalesQuery)->sum('net_amount');
            $todayTrxCount = (clone $todaySalesQuery)->count();
            $todayCashAmount = (float) (clone $todaySalesQuery)->where('payment_method', 'cash')->sum('net_amount');
            $todayQrisAmount = (float) (clone $todaySalesQuery)->where('payment_method', 'qris')->sum('net_amount');

            // Penjualan Cabang Bulan Ini (MTD)
            $monthSalesAmount = (float) CafeSale::where('branch_id', $branch->id)
                ->whereDate('created_at', '>=', $monthStart->toDateString())
                ->whereDate('created_at', '<=', $monthEnd->toDateString())
                ->sum('net_amount');

            $networkTodaySales += $todaySalesAmount;
            $networkMonthSales += $monthSalesAmount;
            $networkCashierCount += $branch->employees_count;

            $avgBasketSize = $todayTrxCount > 0 ? round($todaySalesAmount / $todayTrxCount, 2) : 0.0;

            $branchMatrix[] = [
                'branch' => $branch,
                'stock_qty' => $branchStockQty,
                'valuation' => $branchStockValuation,
                'today_sales' => $todaySalesAmount,
                'today_trx_count' => $todayTrxCount,
                'today_cash' => $todayCashAmount,
                'today_qris' => $todayQrisAmount,
                'avg_basket' => $avgBasketSize,
                'month_sales' => $monthSalesAmount,
            ];
        }

        // 3. Radar Peringatan Kritis ROP (Produk Store yang menyentuh / di bawah ambang ROP)
        $storeBranchIds = $branches->where('type', BranchType::Store)->pluck('id')->toArray();
        $branchProducts = BranchProduct::with(['branch.warehouses', 'variant.product'])
            ->whereIn('branch_id', $storeBranchIds)
            ->where('is_locally_active', true)
            ->where('reorder_point', '>', 0)
            ->get();

        $criticalRopItems = [];
        foreach ($branchProducts as $bp) {
            $whIds = $bp->branch?->warehouses?->pluck('id')->toArray() ?? [];
            $currentStock = (int) StockInventory::where('product_variant_id', $bp->product_variant_id)
                ->whereIn('warehouse_id', $whIds)
                ->sum('qty_on_hand');

            if ($bp->needsReorder((float) $currentStock)) {
                $recommendedTransfer = max(1, (int) ($bp->max_stock - $currentStock));
                $criticalRopItems[] = [
                    'branch_product' => $bp,
                    'branch' => $bp->branch,
                    'variant' => $bp->variant,
                    'current_stock' => $currentStock,
                    'reorder_point' => (int) $bp->reorder_point,
                    'max_stock' => (int) $bp->max_stock,
                    'recommended_transfer' => $recommendedTransfer,
                    'bin_location' => $bp->bin_location ?? 'Gudang Depan',
                ];
            }
        }

        // 4. Pelacakan Surat Jalan / Transfer Stok (STO in-transit & draft)
        $stockTransferOrders = ShiftStockRequest::with(['branch', 'employee', 'shift'])
            ->latest('id')
            ->take(15)
            ->get();

        $pendingStoCount = ShiftStockRequest::whereIn('status', ['draft', 'pending'])->count();

        $summary = [
            'total_valuation' => $totalValuation,
            'dc_valuation' => $dcValuation,
            'store_valuation' => $storeValuation,
            'dc_valuation_pct' => $totalValuation > 0 ? round(($dcValuation / $totalValuation) * 100, 1) : 0,
            'store_valuation_pct' => $totalValuation > 0 ? round(($storeValuation / $totalValuation) * 100, 1) : 0,
            'today_sales' => $networkTodaySales,
            'month_sales' => $networkMonthSales,
            'critical_rop_count' => count($criticalRopItems),
            'pending_sto_count' => $pendingStoCount,
            'total_branches' => $branches->count(),
            'filter_date' => $filterDate->toDateString(),
            'network_cashier_count' => $networkCashierCount,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'summary' => $summary,
                'branch_matrix' => $branchMatrix,
                'critical_rop_items' => $criticalRopItems,
                'stock_transfer_orders' => $stockTransferOrders,
            ]);
        }

        return view('admin.cabang.konsolidasi', compact(
            'summary',
            'branchMatrix',
            'criticalRopItems',
            'stockTransferOrders',
            'filterDate'
        ));
    }
}
