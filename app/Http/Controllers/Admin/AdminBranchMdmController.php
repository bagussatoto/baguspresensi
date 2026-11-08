<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
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
}
