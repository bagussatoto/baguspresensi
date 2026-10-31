<?php

declare(strict_types=1);

namespace Modules\Penjualan\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Penjualan\Application\Requests\StorePosCheckoutRequest;
use Modules\Penjualan\Domain\Contexts\PosCheckoutContext;
use Modules\Penjualan\Domain\Models\PosOrder;
use Modules\Penjualan\Domain\Services\SalesOrderOrchestrator;

class PosCheckoutController extends Controller
{
    public function __construct(
        private readonly SalesOrderOrchestrator $orchestrator
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PosOrder::query()->with(['items.variant', 'customer', 'shift', 'cashier']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('pos_shift_id')) {
            $query->where('pos_shift_id', $request->query('pos_shift_id'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StorePosCheckoutRequest $request): JsonResponse
    {
        $context = PosCheckoutContext::fromArray(array_merge(
            $request->validated(),
            ['cashier_id' => $request->user()?->id]
        ));

        $order = $this->orchestrator->processPosCheckout($context);

        return response()->json([
            'message' => 'Transaksi POS berhasil diselesaikan.',
            'data' => $order,
        ], 201);
    }
}
