<?php

declare(strict_types=1);

namespace Modules\Penjualan\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Penjualan\Application\Requests\StoreSalesOrderRequest;
use Modules\Penjualan\Domain\Contexts\SalesOrderContext;
use Modules\Penjualan\Domain\Models\SalesInvoice;
use Modules\Penjualan\Domain\Models\SalesOrder;
use Modules\Penjualan\Domain\Services\SalesOrderOrchestrator;

class SalesOrderController extends Controller
{
    public function __construct(
        private readonly SalesOrderOrchestrator $orchestrator
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = SalesOrder::query()->with(['customer', 'branch', 'items.variant']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreSalesOrderRequest $request): JsonResponse
    {
        $context = SalesOrderContext::fromArray(array_merge(
            $request->validated(),
            ['created_by' => $request->user()?->id]
        ));

        $so = $this->orchestrator->createSalesOrder($context);

        return response()->json([
            'message' => 'Pesanan Penjualan B2B (Sales Order) berhasil dibuat.',
            'data' => $so,
        ], 201);
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        return response()->json([
            'data' => $salesOrder->load(['customer', 'branch', 'items.variant', 'invoices']),
        ]);
    }

    public function confirm(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $confirmedBy = (int) ($request->user()?->id ?? 1);
        $so = $this->orchestrator->confirmSalesOrder($salesOrder, $confirmedBy);

        return response()->json([
            'message' => 'Pesanan Penjualan berhasil dikonfirmasi.',
            'data' => $so,
        ]);
    }

    public function issueInvoice(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $createdBy = (int) ($request->user()?->id ?? 1);
        $invoice = $this->orchestrator->issueInvoiceFromSalesOrder($salesOrder, $createdBy);

        return response()->json([
            'message' => 'Faktur Tagihan Penjualan berhasil diterbitkan dan stok fisik telah terpotong.',
            'data' => $invoice,
        ], 201);
    }

    public function invoices(Request $request): JsonResponse
    {
        $query = SalesInvoice::query()->with(['customer', 'branch', 'items.variant']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function showInvoice(SalesInvoice $invoice): JsonResponse
    {
        return response()->json([
            'data' => $invoice->load(['customer', 'branch', 'items.variant', 'salesOrder']),
        ]);
    }
}
