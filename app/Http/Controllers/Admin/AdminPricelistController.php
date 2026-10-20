<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Models\Pricelist;
use Modules\MasterData\Domain\Models\PricelistItem;
use Modules\MasterData\Domain\Models\ProductVariant;

/**
 * Controller Admin ERP Maya Graha Kencana: Manajemen Daftar Harga (Pricelists) & Tiering.
 */
class AdminPricelistController extends Controller
{
    /**
     * Tampilkan halaman Daftar Harga (Pricelist) ERP MGK.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Pricelist::with(['items.variant.product'])
            ->orderBy('id', 'asc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        $pricelists = $query->get();
        $totalCount = Pricelist::count();
        $customerTypes = CustomerType::cases();
        $variants = ProductVariant::with('product')->where('is_active', true)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $pricelists->count(),
                'pricelists' => $pricelists,
            ]);
        }

        return view('admin.harga.index', compact('pricelists', 'totalCount', 'customerTypes', 'variants'));
    }

    /**
     * Simpan daftar harga baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'customer_type' => ['required', 'string', 'in:retail,grosir,reseller,proyek'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = ! empty($validated['code'])
            ? trim((string) $validated['code'])
            : 'PL-'.strtoupper(Str::random(5));

        $pricelist = Pricelist::create([
            'name' => trim($validated['name']),
            'code' => $code,
            'customer_type' => $validated['customer_type'],
            'currency' => $validated['currency'] ?? 'IDR',
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Daftar harga {$pricelist->name} berhasil ditambahkan.",
                'pricelist' => $pricelist->load('items'),
            ], 201);
        }

        return redirect()->route('admin.harga')->with('success', "Daftar harga {$pricelist->name} berhasil ditambahkan.");
    }

    /**
     * Update data daftar harga.
     */
    public function update(Request $request, Pricelist $pricelist): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'code' => ['sometimes', 'string', 'max:50'],
            'customer_type' => ['sometimes', 'string', 'in:retail,grosir,reseller,proyek'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $pricelist->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Daftar harga {$pricelist->name} berhasil diperbarui.",
                'pricelist' => $pricelist->fresh('items'),
            ]);
        }

        return redirect()->route('admin.harga')->with('success', "Daftar harga {$pricelist->name} berhasil diperbarui.");
    }

    /**
     * Tambah atau update item harga produk pada pricelist.
     */
    public function storeItem(Request $request, Pricelist $pricelist): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'min_qty' => ['nullable', 'integer', 'min:1'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $item = PricelistItem::updateOrCreate(
            [
                'pricelist_id' => $pricelist->id,
                'product_variant_id' => (int) $validated['product_variant_id'],
            ],
            [
                'unit_price' => (float) $validated['unit_price'],
                'min_qty' => (int) ($validated['min_qty'] ?? 1),
                'discount_percent' => (float) ($validated['discount_percent'] ?? 0),
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item harga berhasil disimpan.',
                'item' => $item->load('variant.product'),
            ]);
        }

        return redirect()->route('admin.harga');
    }

    /**
     * Hapus daftar harga.
     */
    public function destroy(Pricelist $pricelist): JsonResponse|RedirectResponse
    {
        $name = $pricelist->name;
        $pricelist->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Daftar harga {$name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.harga')->with('success', "Daftar harga {$name} berhasil dihapus.");
    }
}
