<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Brand;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;

/**
 * Controller Admin ERP Maya Graha Kencana: Manajemen Katalog Produk & Varian.
 */
class AdminProductController extends Controller
{
    /**
     * Tampilkan halaman Daftar Produk ERP Maya Graha Kencana.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Product::with(['category', 'brand', 'uom', 'variants'])
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhereHas('category', function ($cq) use ($search): void {
                        $cq->where('name', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('brand', function ($bq) use ($search): void {
                        $bq->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('category_id') && $request->input('category_id') !== 'all') {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('brand_id') && $request->input('brand_id') !== 'all') {
            $query->where('brand_id', (int) $request->input('brand_id'));
        }

        $products = $query->get();
        $totalCount = Product::count();
        $categories = Category::where('is_active', true)->orderBy('name', 'asc')->get();
        $brands = Brand::where('is_active', true)->orderBy('name', 'asc')->get();
        $uoms = UnitOfMeasurement::all();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $products->count(),
                'products' => $products,
            ]);
        }

        return view('admin.produk.index', compact(
            'products',
            'totalCount',
            'categories',
            'brands',
            'uoms'
        ));
    }

    /**
     * Simpan data produk baru beserta varian standar.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'uom_id' => ['nullable', 'exists:unit_of_measurements,id'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = ! empty($validated['code'])
            ? trim((string) $validated['code'])
            : 'PRD-'.strtoupper(Str::random(6));

        $product = Product::create([
            'name' => trim($validated['name']),
            'code' => $code,
            'category_id' => (int) $validated['category_id'],
            'brand_id' => ! empty($validated['brand_id']) ? (int) $validated['brand_id'] : null,
            'uom_id' => ! empty($validated['uom_id']) ? (int) $validated['uom_id'] : null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Otomatis buat varian reguler/standar
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $code.'-REG',
            'name' => 'Standar',
            'base_price' => (float) $validated['base_price'],
            'cost_price' => (float) ($validated['cost_price'] ?? 0),
            'is_active' => true,
        ]);

        $product->load(['category', 'brand', 'variants', 'uom']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Produk {$product->name} berhasil ditambahkan.",
                'product' => $product,
            ], 201);
        }

        return redirect()->route('admin.produk')->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    /**
     * Update data produk yang ada.
     */
    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'code' => ['sometimes', 'string', 'max:50'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $product->update($validated);

        if (isset($validated['base_price']) && $product->variants()->exists()) {
            $product->variants()->first()->update([
                'base_price' => (float) $validated['base_price'],
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data produk {$product->name} berhasil diperbarui.",
                'product' => $product->fresh(['category', 'brand', 'variants', 'uom']),
            ]);
        }

        return redirect()->route('admin.produk')->with('success', "Data produk {$product->name} berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/non-aktif produk.
     */
    public function toggleStatus(Product $product): JsonResponse|RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status {$product->name} berhasil diubah menjadi ".($product->is_active ? 'Aktif' : 'Non-Aktif'),
                'is_active' => $product->is_active,
            ]);
        }

        return redirect()->route('admin.produk');
    }

    /**
     * Hapus produk dari katalog.
     */
    public function destroy(Product $product): JsonResponse|RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Produk {$name} berhasil dihapus dari katalog.",
            ]);
        }

        return redirect()->route('admin.produk')->with('success', "Produk {$name} berhasil dihapus.");
    }
}
