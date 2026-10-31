<?php

declare(strict_types=1);

namespace Modules\Distribusi\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Distribusi\Application\Requests\StoreStockTransferDispatchRequest;
use Modules\Distribusi\Application\Requests\StoreStockTransferReceiveRequest;
use Modules\Distribusi\Domain\Contexts\StockTransferDispatchContext;
use Modules\Distribusi\Domain\Contexts\StockTransferReceiveContext;
use Modules\Distribusi\Domain\Models\StockTransfer;
use Modules\Distribusi\Domain\Services\StockTransferService;

class StockTransferController extends Controller
{
    public function __construct(
        private readonly StockTransferService $transferService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = StockTransfer::query()->with(['fromWarehouse', 'toWarehouse', 'items.variant']);

        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->query('from_warehouse_id'));
        }

        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->query('to_warehouse_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreStockTransferDispatchRequest $request): JsonResponse
    {
        $context = StockTransferDispatchContext::fromArray(array_merge(
            $request->validated(),
            ['dispatched_by' => $request->user()?->id]
        ));

        $transfer = $this->transferService->dispatchTransfer($context);

        return response()->json([
            'message' => 'Mutasi transfer berhasil diberangkatkan (Status: In-Transit).',
            'data' => $transfer,
        ], 201);
    }

    public function show(StockTransfer $stockTransfer): JsonResponse
    {
        return response()->json([
            'data' => $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'inTransitWarehouse', 'items.variant']),
        ]);
    }

    public function receive(StoreStockTransferReceiveRequest $request, StockTransfer $stockTransfer): JsonResponse
    {
        $context = StockTransferReceiveContext::fromArray(array_merge(
            $request->validated(),
            [
                'transfer_id' => $stockTransfer->id,
                'received_by' => $request->user()?->id,
            ]
        ));

        $transfer = $this->transferService->receiveTransfer($context);

        return response()->json([
            'message' => "Penerimaan mutasi transfer berhasil diproses (Status: {$transfer->status->label()}).",
            'data' => $transfer,
        ]);
    }
}
