<?php

declare(strict_types=1);

namespace Modules\Kas\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Kas\Application\Requests\StoreKasMasukRequest;
use Modules\Kas\Domain\Contexts\KasMasukContext;
use Modules\Kas\Domain\Enums\KasTransactionType;
use Modules\Kas\Domain\Models\KasTransaction;
use Modules\Kas\Domain\Services\KasMasukService;

class KasMasukController extends Controller
{
    public function __construct(
        private readonly KasMasukService $kasMasukService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = KasTransaction::query()
            ->where('transaction_type', KasTransactionType::Masuk)
            ->with(['kasAccount', 'branch', 'customer', 'lines']);

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

    public function store(StoreKasMasukRequest $request): JsonResponse
    {
        $context = KasMasukContext::fromArray($request->validated());
        $transaction = $this->kasMasukService->execute($context);

        return response()->json([
            'message' => 'Kas masuk berhasil dicatat.',
            'data' => $transaction,
        ], 201);
    }
}
