<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Kas\Domain\Contexts\KasMutasiContext;
use Modules\Kas\Domain\Enums\KasTransactionType;
use Modules\Kas\Domain\Enums\PaymentMethodType;
use Modules\Kas\Domain\Exceptions\InsufficientCashBalanceException;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\Kas\Domain\Models\KasTransaction;
use Modules\Kas\Domain\Models\KasTransfer;

class KasMutasiService
{
    /**
     * Memproses transfer saldo antar akun kas/bank secara atomik.
     *
     * @throws InvalidArgumentException
     * @throws InsufficientCashBalanceException
     */
    public function execute(KasMutasiContext $context): KasTransfer
    {
        if ($context->fromKasAccountId === $context->toKasAccountId) {
            throw new InvalidArgumentException('Akun kas asal dan tujuan transfer tidak boleh sama.');
        }

        if ($context->amount <= 0) {
            throw new InvalidArgumentException('Nominal transfer harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($context) {
            // Lock akun berurutan berdasarkan ID untuk mencegah deadlock
            $accountIds = [$context->fromKasAccountId, $context->toKasAccountId];
            sort($accountIds);

            KasAccount::query()
                ->whereIn('id', $accountIds)
                ->lockForUpdate()
                ->get();

            /** @var KasAccount $fromAccount */
            $fromAccount = KasAccount::findOrFail($context->fromKasAccountId);
            /** @var KasAccount $toAccount */
            $toAccount = KasAccount::findOrFail($context->toKasAccountId);

            $totalDeduction = $context->amount + $context->adminFee;

            if (! $fromAccount->hasSufficientBalance($totalDeduction)) {
                throw new InsufficientCashBalanceException(
                    currentBalance: (float) $fromAccount->current_balance,
                    requestedAmount: $totalDeduction,
                    accountName: $fromAccount->name,
                );
            }

            $date = $context->transferDate ? Carbon::parse($context->transferDate) : Carbon::today();
            $transferNumber = $this->generateTransferNumber($date);

            $kasTransfer = KasTransfer::create([
                'transfer_number' => $transferNumber,
                'from_kas_account_id' => $fromAccount->id,
                'to_kas_account_id' => $toAccount->id,
                'amount' => $context->amount,
                'admin_fee' => $context->adminFee,
                'transfer_date' => $date->toDateString(),
                'reference_number' => $context->referenceNumber,
                'description' => $context->description,
                'authorized_by' => $context->authorizedBy,
            ]);

            // Catat transaksi mutasi keluar pada akun sumber
            KasTransaction::create([
                'transaction_number' => sprintf('MUT-OUT-%s-%04d', $date->format('Ymd'), $kasTransfer->id),
                'kas_account_id' => $fromAccount->id,
                'branch_id' => $fromAccount->branch_id,
                'transaction_type' => KasTransactionType::Mutasi,
                'payment_method' => PaymentMethodType::BankTransfer,
                'amount' => $totalDeduction,
                'user_id' => $context->authorizedBy,
                'reference_number' => $transferNumber,
                'description' => "Mutasi keluar ke [{$toAccount->name}]".($context->adminFee > 0 ? " (Biaya admin: Rp {$context->adminFee})" : ''),
                'transaction_date' => $date->toDateString(),
            ]);

            // Catat transaksi mutasi masuk pada akun tujuan
            KasTransaction::create([
                'transaction_number' => sprintf('MUT-IN-%s-%04d', $date->format('Ymd'), $kasTransfer->id),
                'kas_account_id' => $toAccount->id,
                'branch_id' => $toAccount->branch_id,
                'transaction_type' => KasTransactionType::Mutasi,
                'payment_method' => PaymentMethodType::BankTransfer,
                'amount' => $context->amount,
                'user_id' => $context->authorizedBy,
                'reference_number' => $transferNumber,
                'description' => "Mutasi masuk dari [{$fromAccount->name}]",
                'transaction_date' => $date->toDateString(),
            ]);

            // Potong saldo dari akun sumber dan tambah ke akun tujuan
            $fromAccount->decrement('current_balance', $totalDeduction);
            $toAccount->increment('current_balance', $context->amount);

            return $kasTransfer->fresh(['fromAccount', 'toAccount', 'authorizer']);
        });
    }

    /**
     * Generate format nomor transfer: TRF-{Ymd}-{SEQ}
     */
    private function generateTransferNumber(Carbon $date): string
    {
        $prefix = sprintf('TRF-%s-', $date->format('Ymd'));

        $lastSeq = KasTransfer::query()
            ->where('transfer_number', 'LIKE', "{$prefix}%")
            ->count();

        return sprintf('%s%04d', $prefix, $lastSeq + 1);
    }
}
