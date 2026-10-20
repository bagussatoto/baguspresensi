<?php

declare(strict_types=1);

namespace Modules\MasterData\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\MasterData\Application\Requests\StoreProductVariantRequest;
use Modules\MasterData\Domain\Models\ProductVariant;

class ProductVariantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductVariant::query()->with(['product', 'attributeValues']);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhere('barcode', 'ilike', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(StoreProductVariantRequest $request): JsonResponse
    {
        $variant = ProductVariant::create($request->validated());

        return response()->json([
            'message' => 'Product Variant berhasil dibuat.',
            'data' => $variant,
        ], 201);
    }

    public function show(ProductVariant $variant): JsonResponse
    {
        return response()->json([
            'data' => $variant->load(['product', 'attributeValues', 'pricelistItems']),
        ]);
    }

    public function update(Request $request, ProductVariant $variant): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'weight_grams' => ['sometimes', 'integer', 'min:0'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $variant->update($validated);

        return response()->json([
            'message' => 'Product Variant berhasil diperbarui.',
            'data' => $variant,
        ]);
    }

    public function destroy(ProductVariant $variant): JsonResponse
    {
        $variant->delete();

        return response()->json([
            'message' => 'Product Variant berhasil dihapus.',
        ]);
    }
}
