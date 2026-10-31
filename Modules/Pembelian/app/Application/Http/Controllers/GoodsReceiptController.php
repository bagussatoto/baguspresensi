<?php

declare(strict_types=1);

namespace Modules\Pembelian\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pembelian\Application\Requests\StoreGoodsReceiptRequest;
use Modules\Pembelian\Domain\Contexts\GoodsReceiptContext;
use Modules\Pembelian\Domain\Models\GoodsReceipt;
use Modules\Pembelian\Domain\Services\PurchaseOrderOrchestrator;

class GoodsReceiptController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderOrchestrator $orchestrator
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = GoodsReceipt::query()->with(['supplier', 'warehouse', 'items.variant']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreGoodsReceiptRequest $request): JsonResponse
    {
        $context = GoodsReceiptContext::fromArray(array_merge(
            $request->validated(),
            ['received_by' => $request->user()?->id]
        ));

        $receipt = $this->orchestrator->receiveGoods($context);

        return response()->json([
            'message' => 'Penerimaan fisik barang berhasil diproses, stok dan HPP telah diperbarui.',
            'data' => $receipt,
        ], 201);
    }

    public function show(GoodsReceipt $goodsReceipt): JsonResponse
    {
        return response()->json([
            'data' => $goodsReceipt->load(['supplier', 'warehouse', 'items.variant', 'purchaseOrder']),
        ]);
    }
}
