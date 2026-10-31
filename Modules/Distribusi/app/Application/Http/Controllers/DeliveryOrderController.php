<?php

declare(strict_types=1);

namespace Modules\Distribusi\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Distribusi\Application\Requests\StoreDeliveryOrderRequest;
use Modules\Distribusi\Domain\Models\DeliveryOrder;
use Modules\Distribusi\Domain\Services\StockTransferService;

class DeliveryOrderController extends Controller
{
    public function __construct(
        private readonly StockTransferService $transferService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = DeliveryOrder::query()->with(['customer', 'warehouse', 'items.variant']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreDeliveryOrderRequest $request): JsonResponse
    {
        $deliveryOrder = $this->transferService->createDeliveryOrder(array_merge(
            $request->validated(),
            ['dispatched_by' => $request->user()?->id]
        ));

        return response()->json([
            'message' => 'Surat Jalan (Delivery Order) berhasil diterbitkan dan stok telah dipotong.',
            'data' => $deliveryOrder,
        ], 201);
    }

    public function show(DeliveryOrder $deliveryOrder): JsonResponse
    {
        return response()->json([
            'data' => $deliveryOrder->load(['customer', 'warehouse', 'items.variant']),
        ]);
    }
}
