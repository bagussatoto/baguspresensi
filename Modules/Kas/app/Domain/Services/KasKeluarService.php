<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Kas\Domain\Contexts\KasKeluarContext;
use Modules\Kas\Domain\Enums\KasTransactionType;
use Modules\Kas\Domain\Exceptions\InsufficientCashBalanceException;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\Kas\Domain\Models\KasTransaction;
use Modules\Kas\Domain\Models\KasTransactionLine;

class KasKeluarService
{
    /**
     * Memproses pengeluaran kas keluar secara atomik dengan proteksi saldo minimum.
     *
     * @throws InvalidArgumentException
     * @throws InsufficientCashBalanceException
     */
    public function execute(KasKeluarContext $context): KasTransaction
    {
        if ($context->amount <= 0) {
            throw new InvalidArgumentException('Nominal kas keluar harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($context) {
            /** @var KasAccount $account */
            $account = KasAccount::query()
                ->where('id', $context->kasAccountId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $account->hasSufficientBalance($context->amount)) {
                throw new InsufficientCashBalanceException(
                    currentBalance: (float) $account->current_balance,
                    requestedAmount: $context->amount,
                    accountName: $account->name,
                );
            }

            $date = $context->transactionDate ? Carbon::parse($context->transactionDate) : Carbon::today();
            $transactionNumber = $this->generateTransactionNumber($account->branch_id, $date);

            $kasTransaction = KasTransaction::create([
                'transaction_number' => $transactionNumber,
                'kas_account_id' => $account->id,
                'branch_id' => $context->branchId,
                'transaction_type' => KasTransactionType::Keluar,
                'payment_method' => $context->paymentMethod,
                'amount' => $context->amount,
                'supplier_id' => $context->supplierId,
                'user_id' => $context->userId,
                'reference_number' => $context->referenceNumber,
                'description' => $context->description,
                'transaction_date' => $date->toDateString(),
                'snapshot_payload' => $context->snapshotPayload ?: null,
            ]);

            foreach ($context->lines as $line) {
                KasTransactionLine::create([
                    'kas_transaction_id' => $kasTransaction->id,
                    'reference_type' => $line['reference_type'] ?? null,
                    'reference_id' => isset($line['reference_id']) ? (int) $line['reference_id'] : null,
                    'allocation_amount' => (float) ($line['allocation_amount'] ?? 0),
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            // Kurangi saldo akun kas secara atomik
            $account->decrement('current_balance', $context->amount);

            return $kasTransaction->fresh(['lines', 'kasAccount', 'supplier']);
        });
    }

    /**
     * Generate format nomor transaksi: KK/{BRANCH_ID}/{Ymd}/{SEQ}
     */
    private function generateTransactionNumber(int $branchId, Carbon $date): string
    {
        $prefix = sprintf('KK-%03d-%s-', $branchId, $date->format('Ymd'));

        $lastSeq = KasTransaction::query()
            ->where('transaction_number', 'LIKE', "{$prefix}%")
            ->count();

        return sprintf('%s%04d', $prefix, $lastSeq + 1);
    }
}
