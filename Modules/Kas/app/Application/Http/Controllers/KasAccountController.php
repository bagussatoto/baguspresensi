<?php

declare(strict_types=1);

namespace Modules\Kas\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Kas\Application\Requests\StoreKasAccountRequest;
use Modules\Kas\Domain\Models\KasAccount;

class KasAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KasAccount::query()->with('branch');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreKasAccountRequest $request): JsonResponse
    {
        $account = KasAccount::create($request->validated());

        return response()->json([
            'message' => 'Akun kas/bank berhasil dibuat.',
            'data' => $account->load('branch'),
        ], 201);
    }

    public function show(KasAccount $account): JsonResponse
    {
        return response()->json([
            'data' => $account->load('branch'),
        ]);
    }
}
