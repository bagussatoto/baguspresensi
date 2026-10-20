<?php

declare(strict_types=1);

namespace Modules\MasterData\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\MasterData\Application\Requests\StorePricelistRequest;
use Modules\MasterData\Domain\Models\Pricelist;
use Modules\MasterData\Domain\Models\PricelistItem;

class PricelistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Pricelist::query()->with('items.variant');

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->query('customer_type'));
        }

        return response()->json($query->paginate(20));
    }

    public function store(StorePricelistRequest $request): JsonResponse
    {
        $pricelist = Pricelist::create($request->validated());

        return response()->json([
            'message' => 'Pricelist berhasil dibuat.',
            'data' => $pricelist,
        ], 201);
    }

    public function show(Pricelist $pricelist): JsonResponse
    {
        return response()->json([
            'data' => $pricelist->load('items.variant'),
        ]);
    }

    public function addItem(Request $request, Pricelist $pricelist): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'min_qty' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $item = PricelistItem::updateOrCreate(
            [
                'pricelist_id' => $pricelist->id,
                'product_variant_id' => $validated['product_variant_id'],
                'min_qty' => $validated['min_qty'],
            ],
            [
                'unit_price' => $validated['unit_price'],
                'discount_percent' => $validated['discount_percent'] ?? 0.0,
            ]
        );

        return response()->json([
            'message' => 'Item tier harga berhasil ditambahkan ke pricelist.',
            'data' => $item,
        ], 201);
    }

    public function destroy(Pricelist $pricelist): JsonResponse
    {
        $pricelist->delete();

        return response()->json([
            'message' => 'Pricelist berhasil dihapus.',
        ]);
    }
}
