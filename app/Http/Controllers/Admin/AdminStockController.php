<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;

/**
 * Controller Admin ERP Maya Graha Kencana: Manajemen Stok Limit & Inventaris Fisik.
 */
class AdminStockController extends Controller
{
    /**
     * Tampilkan halaman Stok Limit & Inventaris Gudang ERP MGK.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = StockInventory::with(['warehouse.branch', 'variant.product.category'])
            ->orderBy('id', 'asc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->whereHas('variant', function ($vq) use ($search): void {
                $vq->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search): void {
                        $pq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('code', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($request->filled('warehouse_id') && $request->input('warehouse_id') !== 'all') {
            $query->where('warehouse_id', (int) $request->input('warehouse_id'));
        }

        if ($request->filled('filter_stock')) {
            $filter = $request->input('filter_stock');
            if ($filter === 'low') {
                $query->whereColumn('qty_on_hand', '<=', 'safety_stock');
            } elseif ($filter === 'out') {
                $query->where('qty_on_hand', '<=', 0);
            } elseif ($filter === 'safe') {
                $query->whereColumn('qty_on_hand', '>', 'safety_stock');
            }
        }

        $stocks = $query->get();
        $warehouses = Warehouse::with('branch')->get();

        $metrics = [
            'total_items' => StockInventory::count(),
            'low_stock_count' => StockInventory::whereColumn('qty_on_hand', '<=', 'safety_stock')->count(),
            'out_of_stock_count' => StockInventory::where('qty_on_hand', '<=', 0)->count(),
            'total_qty' => (int) StockInventory::sum('qty_on_hand'),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $stocks->count(),
                'metrics' => $metrics,
                'stocks' => $stocks,
            ]);
        }

        return view('admin.stok.index', compact('stocks', 'warehouses', 'metrics'));
    }

    /**
     * Update batas safety stock atau lakukan penyesuaian stok opname.
     */
    public function update(Request $request, StockInventory $stock): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'safety_stock' => ['sometimes', 'integer', 'min:0'],
            'qty_on_hand' => ['sometimes', 'integer', 'min:0'],
        ]);

        $stock->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Batas stok & kuantitas berhasil diperbarui.',
                'stock' => $stock->fresh(['warehouse', 'variant.product']),
            ]);
        }

        return redirect()->route('admin.stok-limit')->with('success', 'Batas stok berhasil diperbarui.');
    }
}
