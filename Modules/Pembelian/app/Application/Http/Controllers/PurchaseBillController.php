<?php

declare(strict_types=1);

namespace Modules\Pembelian\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pembelian\Domain\Models\PurchaseBill;

class PurchaseBillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseBill::query()->with(['supplier', 'items.variant']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show(PurchaseBill $bill): JsonResponse
    {
        return response()->json([
            'data' => $bill->load(['supplier', 'items.variant', 'purchaseOrder']),
        ]);
    }
}
