<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

use Illuminate\Support\Facades\DB;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafePayment;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\PcStation;
use Modules\MasterData\Domain\Models\StockInventory;

/**
 * Domain Service: Menangani logika checkout penjualan kasir warnet & F&B.
 * Mengisolasi aturan bisnis agregat penjualan, pembayaran, pengurangan stok, dan status PC.
 */
class CafeCheckoutService
{
    /**
     * Eksekusi transaksi checkout kasir dengan aturan domain DDD.
     *
     * @param  array{
     *     employee_id: int,
     *     customer_name: string,
     *     pc_station_id: ?int,
     *     payment_method: string,
     *     amount_paid?: ?float,
     *     payment_reference?: ?string,
     *     cashier_notes?: ?string,
     *     finish_pc_session?: ?bool,
     *     items: array<array{variant_id: ?int, item_type: string, name: string, qty: int, price: float}>
     * }  $data
     */
    public function execute(array $data): CafeSale
    {
        return DB::transaction(function () use ($data) {
            $invoiceNumber = 'INV-NET-'.date('YmdHis').'-'.rand(10, 99);
            $paymentNumber = 'PAY-'.date('YmdHis').'-'.rand(100, 999);
            $totalBilling = 0.0;
            $totalFnb = 0.0;
            $gross = 0.0;

            foreach ($data['items'] as $item) {
                $subtotal = (float) $item['price'] * (int) $item['qty'];
                $gross += $subtotal;
                if ($item['item_type'] === 'billing') {
                    $totalBilling += $subtotal;
                } else {
                    $totalFnb += $subtotal;
                }
            }

            $amountPaid = isset($data['amount_paid']) && (float) $data['amount_paid'] > 0
                ? (float) $data['amount_paid']
                : $gross;
            $changeAmount = max(0.0, $amountPaid - $gross);

            $employee = Employee::find($data['employee_id']);
            $branchMgk = Branch::where('code', 'NET-PST')->first() ?? Branch::first();
            $branchId = $employee?->branch_id ?? $branchMgk?->id ?? 3;

            $sale = CafeSale::create([
                'invoice_number' => $invoiceNumber,
                'branch_id' => $branchId,
                'employee_id' => $data['employee_id'],
                'customer_name' => $data['customer_name'],
                'pc_station_id' => $data['pc_station_id'] ?? null,
                'total_billing' => $totalBilling,
                'total_fnb' => $totalFnb,
                'gross_amount' => $gross,
                'discount_amount' => 0.0,
                'tax_amount' => 0.0,
                'net_amount' => $gross,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'payment_reference' => $data['payment_reference'] ?? null,
                'cashier_notes' => $data['cashier_notes'] ?? null,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
            ]);

            // Catat pencatatan transaksi kasir ke entitas pembayaran (CafePayment)
            CafePayment::create([
                'cafe_sale_id' => $sale->id,
                'payment_number' => $paymentNumber,
                'payment_method' => $data['payment_method'],
                'amount_due' => $gross,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'payment_reference' => $data['payment_reference'] ?? null,
                'status' => 'completed',
                'paid_at' => now(),
                'cashier_employee_id' => $data['employee_id'],
            ]);

            // Item rincian pesanan & pengurangan stok fisik F&B
            foreach ($data['items'] as $item) {
                CafeSaleItem::create([
                    'cafe_sale_id' => $sale->id,
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'item_type' => $item['item_type'],
                    'item_name' => $item['name'],
                    'qty' => (int) $item['qty'],
                    'unit_price' => (float) $item['price'],
                    'subtotal' => (float) $item['price'] * (int) $item['qty'],
                ]);

                if (! empty($item['variant_id']) && $item['item_type'] === 'fnb') {
                    $inv = StockInventory::where('product_variant_id', $item['variant_id'])->first();
                    if ($inv) {
                        $inv->decrement('qty_on_hand', (int) $item['qty']);
                    }
                }
            }

            // Aturan status PC Station
            if (! empty($data['pc_station_id'])) {
                $pc = PcStation::find($data['pc_station_id']);
                if ($pc) {
                    if (! empty($data['finish_pc_session'])) {
                        // Selesai bermain dan bayar di kasir
                        $pc->update([
                            'status' => 'available',
                            'current_user_name' => null,
                            'started_at' => null,
                            'duration_minutes' => 0,
                            'billing_amount' => 0.0,
                        ]);
                    } else {
                        // Sewa sesi baru via kasir
                        $pc->update([
                            'status' => 'occupied',
                            'current_user_name' => $data['customer_name'],
                            'started_at' => now(),
                            'billing_amount' => (float) $pc->billing_amount + $totalBilling,
                        ]);
                    }
                }
            }

            return $sale;
        });
    }
}
