<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

use Illuminate\Support\Facades\DB;
use Modules\MasterData\Domain\Exceptions\CreditLimitExceededException;
use Modules\MasterData\Domain\Exceptions\OverdueInvoiceBlockedException;
use Modules\MasterData\Domain\Models\Customer;

class CustomerCreditGuard
{
    /**
     * Validasi kelayakan kredit pelanggan. Melemparkan exception jika plafon habis atau macet.
     *
     * @throws CreditLimitExceededException
     * @throws OverdueInvoiceBlockedException
     */
    public function validate(Customer $customer, float $orderAmount, bool $checkOverdue = true): void
    {
        if ($checkOverdue && $this->hasOverdueInvoices($customer)) {
            throw new OverdueInvoiceBlockedException((int) $customer->id);
        }

        $creditLimit = (float) $customer->credit_limit;
        $currentUsed = (float) $customer->current_credit_used;

        if ($creditLimit <= 0.0) {
            throw new CreditLimitExceededException(
                creditLimit: $creditLimit,
                currentUsed: $currentUsed,
                requestedAmount: $orderAmount,
                message: "Konsumen [{$customer->name}] tidak memiliki plafon kredit (plafon Rp 0). Transaksi wajib tunai."
            );
        }

        if (($currentUsed + $orderAmount) > $creditLimit) {
            throw new CreditLimitExceededException(
                creditLimit: $creditLimit,
                currentUsed: $currentUsed,
                requestedAmount: $orderAmount,
                message: "Plafon kredit konsumen [{$customer->name}] tidak mencukupi. Plafon: Rp {$creditLimit}, Terpakai: Rp {$currentUsed}, Order: Rp {$orderAmount}."
            );
        }
    }

    /**
     * Cek boolean apakah transaksi kredit dapat dilanjutkan.
     */
    public function canProceed(Customer $customer, float $orderAmount, bool $checkOverdue = true): bool
    {
        try {
            $this->validate($customer, $orderAmount, $checkOverdue);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Cek apakah ada faktur piutang yang jatuh tempo dan belum lunas.
     * (Akan memeriksa tabel sales_invoices jika tabel tersebut sudah ada di database).
     */
    public function hasOverdueInvoices(Customer $customer): bool
    {
        if (! DB::getSchemaBuilder()->hasTable('sales_invoices')) {
            return false;
        }

        return DB::table('sales_invoices')
            ->where('customer_id', $customer->id)
            ->where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->exists();
    }

    /**
     * Tambah penggunaan kredit secara atomik (saat sales invoice diterbitkan).
     */
    public function consumeCredit(Customer $customer, float $amount): void
    {
        DB::transaction(function () use ($customer, $amount) {
            $freshCustomer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            $freshCustomer->current_credit_used = (float) $freshCustomer->current_credit_used + $amount;
            $freshCustomer->save();
        });
    }

    /**
     * Pulihkan plafon kredit konsumen secara atomik (saat piutang dilunasi di Modul Kas).
     */
    public function releaseCredit(Customer $customer, float $amount): void
    {
        DB::transaction(function () use ($customer, $amount) {
            $freshCustomer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            $newUsed = max(0.0, (float) $freshCustomer->current_credit_used - $amount);
            $freshCustomer->current_credit_used = $newUsed;
            $freshCustomer->save();
        });
    }
}
