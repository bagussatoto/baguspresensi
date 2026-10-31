<?php

declare(strict_types=1);

namespace Modules\Penjualan\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Penjualan\Application\Requests\ClosePosShiftRequest;
use Modules\Penjualan\Application\Requests\StorePosShiftOpenRequest;
use Modules\Penjualan\Domain\Contexts\PosShiftContext;
use Modules\Penjualan\Domain\Models\PosShift;
use Modules\Penjualan\Domain\Services\PosShiftService;

class PosShiftController extends Controller
{
    public function __construct(
        private readonly PosShiftService $shiftService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PosShift::query()->with(['branch', 'kasAccount', 'cashier']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function open(StorePosShiftOpenRequest $request): JsonResponse
    {
        $context = PosShiftContext::fromArray(array_merge(
            $request->validated(),
            ['cashier_id' => $request->user()?->id ?? 1]
        ));

        $shift = $this->shiftService->openShift($context);

        return response()->json([
            'message' => 'Sesi shift laci kasir berhasil dibuka.',
            'data' => $shift,
        ], 201);
    }

    public function close(ClosePosShiftRequest $request, PosShift $posShift): JsonResponse
    {
        $shift = $this->shiftService->closeShift(
            shiftId: $posShift->id,
            actualCash: (float) $request->input('actual_cash'),
            notes: $request->input('notes')
        );

        return response()->json([
            'message' => 'Sesi shift kasir berhasil ditutup.',
            'data' => $shift,
        ]);
    }

    public function reconcile(Request $request, PosShift $posShift): JsonResponse
    {
        $shift = $this->shiftService->reconcileShift(
            shiftId: $posShift->id,
            notes: $request->input('notes')
        );

        return response()->json([
            'message' => 'Rekonsiliasi shift kasir berhasil diverifikasi.',
            'data' => $shift,
        ]);
    }

    public function show(PosShift $posShift): JsonResponse
    {
        return response()->json([
            'data' => $posShift->load(['branch', 'kasAccount', 'cashier', 'orders']),
        ]);
    }
}
