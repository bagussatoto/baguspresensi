<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

use App\Models\ShiftStockRequest;
use Illuminate\Support\Facades\Log;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\ProductVariant;

/**
 * Service: Otomatisasi Penerbitan Dokumen Permintaan Pasokan (Auto-Draft STO).
 * Beroperasi saat sisa stok fisik cabang menyentuh atau berada di bawah Reorder Point (ROP).
 */
class AutoStockTransferOrderService
{
    /**
     * Evaluasi sisa stok cabang dan buatkan draft permintaan pasokan otomatis jika menyentuh ROP.
     */
    public function evaluateAndCreateDraftSto(
        ProductVariant $variant,
        int $branchId,
        float $availableStock,
        ?int $employeeId = null,
        ?int $shiftId = null
    ): ?ShiftStockRequest {
        $branchProduct = BranchProduct::where('branch_id', $branchId)
            ->where('product_variant_id', $variant->id)
            ->first();

        // Jika tidak ada konfigurasi lokalisasi atau produk tidak aktif di cabang, abaikan
        if (! $branchProduct || ! $branchProduct->is_locally_active || $branchProduct->reorder_point <= 0) {
            return null;
        }

        // Cek apakah stok menyentuh atau berada di bawah ROP
        if (! $branchProduct->needsReorder($availableStock)) {
            return null;
        }

        // Anti-duplikasi: Cek apakah sudah ada permintaan pending untuk produk dan cabang ini
        $existingPending = ShiftStockRequest::where('branch_id', $branchId)
            ->where('item_name', $variant->name)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            Log::info("Auto-Draft STO diabaikan: Sudah terdapat tiket pending ({$existingPending->request_number}) untuk {$variant->name} di cabang ID {$branchId}.");

            return $existingPending;
        }

        // Hitung kuantitas kebutuhan pasok (Reorder Quantity)
        // Rumus: Kebutuhan = max_stock - availableStock (atau 2 x ROP jika max_stock belum diset)
        $targetStock = $branchProduct->max_stock > $branchProduct->reorder_point
            ? $branchProduct->max_stock
            : ($branchProduct->reorder_point * 2);

        $reorderQty = max(1, (int) round($targetStock - $availableStock));

        $branch = Branch::find($branchId);
        $branchName = $branch ? $branch->name : "Cabang ID #{$branchId}";

        // Cari Distribution Center (DC) utama sebagai referensi pasokan
        $dcBranch = Branch::where('type', BranchType::Dc)->where('is_active', true)->first();
        $dcName = $dcBranch ? $dcBranch->name : 'Distribution Center Pusat';

        $reqNumber = 'STO-ROP-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
        $categoryName = $variant->product?->category?->name ?? 'F&B dan Ritel';

        $notes = "[AUTO-ROP MDM] Stok tersisa {$availableStock} unit (Batas ROP: {$branchProduct->reorder_point} unit, Kapasitas Max: {$branchProduct->max_stock} unit, Rak: {$branchProduct->bin_location}). Rekomendasi pasokan pasok ulang otomatis dari {$dcName} ke {$branchName}.";

        $stoRequest = ShiftStockRequest::create([
            'request_number' => $reqNumber,
            'employee_id' => $employeeId,
            'branch_id' => $branchId,
            'shift_id' => $shiftId,
            'item_category' => $categoryName,
            'item_name' => $variant->name,
            'qty_requested' => $reorderQty,
            'unit' => 'pcs',
            'urgency' => 'urgent',
            'notes' => $notes,
            'status' => 'pending',
        ]);

        Log::info("Auto-Draft STO Berhasil Diterbitkan: {$reqNumber} untuk {$variant->name} ({$reorderQty} unit) ke cabang {$branchName}.");

        return $stoRequest;
    }
}
