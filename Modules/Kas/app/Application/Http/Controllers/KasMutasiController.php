<?php

declare(strict_types=1);

namespace Modules\Kas\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Kas\Application\Requests\StoreKasMutasiRequest;
use Modules\Kas\Domain\Contexts\KasMutasiContext;
use Modules\Kas\Domain\Exceptions\InsufficientCashBalanceException;
use Modules\Kas\Domain\Models\KasTransfer;
use Modules\Kas\Domain\Services\KasMutasiService;

class KasMutasiController extends Controller
{
    public function __construct(
        private readonly KasMutasiService $kasMutasiService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = KasTransfer::query()->with(['fromAccount', 'toAccount', 'authorizer']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transfer_date', [$request->query('start_date'), $request->query('end_date')]);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreKasMutasiRequest $request): JsonResponse
    {
        try {
            $context = KasMutasiContext::fromArray($request->validated());
            $transfer = $this->kasMutasiService->execute($context);

            return response()->json([
                'message' => 'Mutasi kas/bank berhasil diproses.',
                'data' => $transfer,
            ], 201);
        } catch (InsufficientCashBalanceException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
