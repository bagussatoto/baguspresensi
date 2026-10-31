<?php

declare(strict_types=1);

namespace Modules\Pembelian\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pembelian\Application\Requests\StorePurchaseOrderRequest;
use Modules\Pembelian\Domain\Contexts\PurchaseOrderContext;
use Modules\Pembelian\Domain\Models\PurchaseOrder;
use Modules\Pembelian\Domain\Services\PurchaseOrderOrchestrator;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderOrchestrator $orchestrator
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::query()->with(['supplier', 'branch', 'items.variant']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $context = PurchaseOrderContext::fromArray(array_merge(
            $request->validated(),
            ['created_by' => $request->user()?->id]
        ));

        $po = $this->orchestrator->createPurchaseOrder($context);

        return response()->json([
            'message' => 'Purchase Order berhasil dibuat.',
            'data' => $po,
        ], 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return response()->json([
            'data' => $purchaseOrder->load(['supplier', 'branch', 'items.variant', 'goodsReceipts', 'bills']),
        ]);
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $approvedBy = (int) ($request->user()?->id ?? 1);
        $po = $this->orchestrator->approvePurchaseOrder($purchaseOrder, $approvedBy);

        return response()->json([
            'message' => 'Purchase Order berhasil disetujui.',
            'data' => $po,
        ]);
    }
}
