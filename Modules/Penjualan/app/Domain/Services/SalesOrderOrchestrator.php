<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Services;

use Carbon\Carbon;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Kas\Domain\Contexts\KasMasukContext;
use Modules\Kas\Domain\Enums\PaymentMethodType;
use Modules\Kas\Domain\Services\KasMasukService;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Services\CustomerCreditGuard;
use Modules\Penjualan\Domain\Contexts\PosCheckoutContext;
use Modules\Penjualan\Domain\Contexts\SalesOrderContext;
use Modules\Penjualan\Domain\Enums\SalesOrderStatus;
use Modules\Penjualan\Domain\Models\PosOrder;
use Modules\Penjualan\Domain\Models\PosOrderItem;
use Modules\Penjualan\Domain\Models\PosShift;
use Modules\Penjualan\Domain\Models\SalesInvoice;
use Modules\Penjualan\Domain\Models\SalesInvoiceItem;
use Modules\Penjualan\Domain\Models\SalesOrder;
use Modules\Penjualan\Domain\Models\SalesOrderItem;
use Modules\Penjualan\Domain\Pipelines\CalculatePricingAndTaxPipe;
use Modules\Penjualan\Domain\Pipelines\FreezeJsonbSnapshotPipe;
use Modules\Penjualan\Domain\Pipelines\GenerateAtomicInvoiceNumberPipe;
use Modules\Penjualan\Domain\Pipelines\ValidateCustomerCreditPipe;
use Modules\Penjualan\Domain\Pipelines\ValidateStockAvailabilityPipe;
use Modules\Penjualan\Events\PosOrderCompletedEvent;
use Modules\Penjualan\Events\SalesInvoiceIssuedEvent;

class SalesOrderOrchestrator
{
    public function __construct(
        private readonly KasMasukService $kasMasukService,
        private readonly CustomerCreditGuard $customerCreditGuard,
    ) {}

    /**
     * Memproses transaksi POS Kasir Retail Cepat (Stok terpotong, Uang masuk Kas, Shift drawer bertambah).
     */
    public function processPosCheckout(PosCheckoutContext $context): PosOrder
    {
        if (empty($context->items)) {
            throw new InvalidArgumentException('Transaksi POS harus memiliki minimal 1 item.');
        }

        return DB::transaction(function () use ($context) {
            $shift = null;
            if ($context->posShiftId !== null) {
                /** @var PosShift $shift */
                $shift = PosShift::query()->where('id', $context->posShiftId)->lockForUpdate()->firstOrFail();
                if (! $shift->isOpen()) {
                    throw new InvalidArgumentException("Sesi shift #{$shift->shift_number} telah ditutup. Transaksi kasir tidak dapat dilakukan.");
                }
            }

            // Jalankan validasi stok & kalkulasi harga
            $pipelineResult = app(Pipeline::class)
                ->send([
                    'warehouse_id' => $context->warehouseId,
                    'items' => $context->items,
                    'apply_tax' => false,
                ])
                ->through([
                    ValidateStockAvailabilityPipe::class,
                    CalculatePricingAndTaxPipe::class,
                    FreezeJsonbSnapshotPipe::class,
                ])
                ->thenReturn();

            $netAmount = (float) $pipelineResult['net_amount'];
            $totalAmount = (float) $pipelineResult['total_amount'];
            $discountAmount = (float) $pipelineResult['discount_amount'];

            $cashPaid = $context->cashPaid;
            $changeGiven = 0.0;
            if ($context->paymentMethod === PaymentMethodType::Tunai) {
                if ($cashPaid < $netAmount) {
                    throw new InvalidArgumentException(
                        'Uang tunai yang dibayarkan (Rp '.number_format($cashPaid, 2, ',', '.').') kurang dari total tagihan (Rp '.number_format($netAmount, 2, ',', '.').').'
                    );
                }
                $changeGiven = max(0.0, $cashPaid - $netAmount);
            } else {
                $cashPaid = $netAmount;
            }

            $date = Carbon::now();
            $orderNumber = $this->generatePosOrderNumber($context->branchId, $date);

            $order = PosOrder::create([
                'pos_shift_id' => $context->posShiftId,
                'branch_id' => $context->branchId,
                'warehouse_id' => $context->warehouseId,
                'order_number' => $orderNumber,
                'customer_id' => $context->customerId,
                'payment_method' => $context->paymentMethod,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => 0.0,
                'net_amount' => $netAmount,
                'cash_paid' => $cashPaid,
                'change_given' => $changeGiven,
                'cashier_id' => $context->cashierId,
                'snapshot_payload' => $pipelineResult['snapshot_payload'],
            ]);

            // Potong stok gudang secara fisik untuk setiap baris produk
            foreach ($pipelineResult['items'] as $itemData) {
                PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'qty' => (int) $itemData['qty'],
                    'unit_price' => (float) $itemData['unit_price'],
                    'discount_amount' => (float) $itemData['discount_amount'],
                    'subtotal' => (float) $itemData['subtotal'],
                ]);

                StockInventory::where('warehouse_id', $context->warehouseId)
                    ->where('product_variant_id', $itemData['product_variant_id'])
                    ->decrement('qty_on_hand', (int) $itemData['qty']);
            }

            // Update shift drawer laci kasir jika shift ada
            if ($shift instanceof PosShift) {
                $shift->total_sales += $netAmount;
                if ($context->paymentMethod === PaymentMethodType::Tunai) {
                    $shift->total_cash_sales += $netAmount;
                    $shift->expected_cash += $netAmount;
                } else {
                    $shift->total_non_cash_sales += $netAmount;
                }
                $shift->save();

                // Catat transaksi uang masuk otomatis di Modul Kas
                $kasTx = $this->kasMasukService->execute(new KasMasukContext(
                    kasAccountId: $shift->kas_account_id,
                    branchId: $context->branchId,
                    paymentMethod: $context->paymentMethod,
                    amount: $netAmount,
                    customerId: $context->customerId,
                    userId: $context->cashierId,
                    referenceNumber: $orderNumber,
                    description: "Penerimaan POS Nota #{$orderNumber}",
                    snapshotPayload: $pipelineResult['snapshot_payload'],
                ));

                $order->kas_transaction_id = $kasTx->id;
                $order->save();
            }

            event(new PosOrderCompletedEvent($order));

            return $order->fresh(['items.variant', 'shift', 'customer', 'branch']);
        });
    }

    /**
     * Membuat Pesanan Penjualan B2B Kredit (Sales Order).
     */
    public function createSalesOrder(SalesOrderContext $context): SalesOrder
    {
        if (empty($context->items)) {
            throw new InvalidArgumentException('Pesanan Penjualan harus memiliki minimal 1 item.');
        }

        $customer = Customer::findOrFail($context->customerId);

        // Validasi kelayakan kredit, stok, dan perhitungan harga
        $pipelineResult = app(Pipeline::class)
            ->send([
                'customer' => $customer,
                'warehouse_id' => $context->warehouseId,
                'items' => $context->items,
                'apply_tax' => true,
                'total_amount' => 0.0,
            ])
            ->through([
                CalculatePricingAndTaxPipe::class,
                ValidateCustomerCreditPipe::class,
                ValidateStockAvailabilityPipe::class,
                FreezeJsonbSnapshotPipe::class,
            ])
            ->thenReturn();

        return DB::transaction(function () use ($context, $customer, $pipelineResult) {
            $date = $context->orderDate ? Carbon::parse($context->orderDate) : Carbon::today();
            $orderNumber = $this->generateSalesOrderNumber($context->branchId, $date);

            $termDays = $context->paymentTermDays ?? (int) $customer->payment_term_days;

            $so = SalesOrder::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'branch_id' => $context->branchId,
                'warehouse_id' => $context->warehouseId,
                'status' => SalesOrderStatus::Draft,
                'order_date' => $date->toDateString(),
                'payment_term_days' => $termDays,
                'total_amount' => $pipelineResult['total_amount'],
                'tax_amount' => $pipelineResult['tax_amount'],
                'net_amount' => $pipelineResult['net_amount'],
                'notes' => $context->notes,
                'created_by' => $context->createdBy,
                'snapshot_payload' => $pipelineResult['snapshot_payload'],
            ]);

            foreach ($pipelineResult['items'] as $itemData) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'qty' => (int) $itemData['qty'],
                    'unit_price' => (float) $itemData['unit_price'],
                    'discount_amount' => (float) $itemData['discount_amount'],
                    'subtotal' => (float) $itemData['subtotal'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            return $so->fresh(['items.variant', 'customer', 'branch']);
        });
    }

    /**
     * Konfirmasi pesanan penjualan B2B.
     */
    public function confirmSalesOrder(SalesOrder $so, int $confirmedBy): SalesOrder
    {
        if ($so->status !== SalesOrderStatus::Draft) {
            throw new InvalidArgumentException("Hanya SO berstatus Draft yang dapat dikonfirmasi. Status saat ini: {$so->status->label()}.");
        }

        $so->status = SalesOrderStatus::Confirmed;
        $so->confirmed_by = $confirmedBy;
        $so->save();

        return $so->fresh();
    }

    /**
     * Menerbitkan Faktur Tagihan Penjualan (Sales Invoice), potong stok fisik, dan kurangi plafon kredit pelanggan.
     */
    public function issueInvoiceFromSalesOrder(SalesOrder $so, int $createdBy): SalesInvoice
    {
        return DB::transaction(function () use ($so, $createdBy) {
            $freshSo = SalesOrder::with(['items', 'customer'])->lockForUpdate()->findOrFail($so->id);

            if ($freshSo->status === SalesOrderStatus::Invoiced) {
                throw new InvalidArgumentException("Faktur untuk pesanan #{$freshSo->order_number} sudah pernah diterbitkan sebelumnya.");
            }

            $date = Carbon::today();
            $dueDate = $date->copy()->addDays((int) $freshSo->payment_term_days);

            $pipelineData = app(Pipeline::class)
                ->send([
                    'branch_id' => $freshSo->branch_id,
                    'invoice_date' => $date->toDateString(),
                ])
                ->through([
                    GenerateAtomicInvoiceNumberPipe::class,
                ])
                ->thenReturn();

            $invoice = SalesInvoice::create([
                'invoice_number' => $pipelineData['invoice_number'],
                'sales_order_id' => $freshSo->id,
                'customer_id' => $freshSo->customer_id,
                'branch_id' => $freshSo->branch_id,
                'payment_status' => 'unpaid',
                'invoice_date' => $date->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'total_amount' => $freshSo->total_amount,
                'tax_amount' => $freshSo->tax_amount,
                'net_amount' => $freshSo->net_amount,
                'paid_amount' => 0.00,
                'remaining_balance' => $freshSo->net_amount,
                'notes' => "Faktur atas Pesanan Penjualan #{$freshSo->order_number}",
                'created_by' => $createdBy,
                'snapshot_payload' => $freshSo->snapshot_payload,
            ]);

            foreach ($freshSo->items as $item) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_variant_id' => $item->product_variant_id,
                    'qty' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);

                // Potong stok fisik gudang
                StockInventory::where('warehouse_id', $freshSo->warehouse_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->decrement('qty_on_hand', $item->qty);
            }

            // Tambah pemakaian kredit konsumen (mengurangi sisa plafon)
            $this->customerCreditGuard->consumeCredit($freshSo->customer, (float) $freshSo->net_amount);

            $freshSo->status = SalesOrderStatus::Invoiced;
            $freshSo->save();

            event(new SalesInvoiceIssuedEvent($invoice));

            return $invoice->fresh(['items.variant', 'customer', 'branch', 'salesOrder']);
        });
    }

    private function generatePosOrderNumber(int $branchId, Carbon $date): string
    {
        $prefix = sprintf('POS-%03d-%s-', $branchId, $date->format('Ymd'));
        $count = PosOrder::where('order_number', 'LIKE', "{$prefix}%")->count();

        return sprintf('%s%04d', $prefix, $count + 1);
    }

    private function generateSalesOrderNumber(int $branchId, Carbon $date): string
    {
        $prefix = sprintf('SO-%03d-%s-', $branchId, $date->format('Ymd'));
        $count = SalesOrder::where('order_number', 'LIKE', "{$prefix}%")->count();

        return sprintf('%s%04d', $prefix, $count + 1);
    }
}
