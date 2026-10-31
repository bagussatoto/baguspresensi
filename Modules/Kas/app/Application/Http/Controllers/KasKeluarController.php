<?php

declare(strict_types=1);

namespace Modules\Kas\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Kas\Application\Requests\StoreKasKeluarRequest;
use Modules\Kas\Domain\Contexts\KasKeluarContext;
use Modules\Kas\Domain\Enums\KasTransactionType;
use Modules\Kas\Domain\Exceptions\InsufficientCashBalanceException;
use Modules\Kas\Domain\Models\KasTransaction;
use Modules\Kas\Domain\Services\KasKeluarService;

class KasKeluarController extends Controller
{
    public function __construct(
        private readonly KasKeluarService $kasKeluarService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = KasTransaction::query()
            ->where('transaction_type', KasTransactionType::Keluar)
            ->with(['kasAccount', 'branch', 'supplier', 'lines']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('kas_account_id')) {
            $query->where('kas_account_id', $request->query('kas_account_id'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [$request->query('start_date'), $request->query('end_date')]);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreKasKeluarRequest $request): JsonResponse
    {
        try {
            $context = KasKeluarContext::fromArray($request->validated());
            $transaction = $this->kasKeluarService->execute($context);

            return response()->json([
                'message' => 'Kas keluar berhasil dicatat.',
                'data' => $transaction,
            ], 201);
        } catch (InsufficientCashBalanceException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
