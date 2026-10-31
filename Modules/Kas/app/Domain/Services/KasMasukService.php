<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Kas\Domain\Contexts\KasMasukContext;
use Modules\Kas\Domain\Enums\KasTransactionType;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\Kas\Domain\Models\KasTransaction;
use Modules\Kas\Domain\Models\KasTransactionLine;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Services\CustomerCreditGuard;

class KasMasukService
{
    public function __construct(
        public readonly CustomerCreditGuard $customerCreditGuard
    ) {}

    /**
     * Memproses penerimaan kas masuk secara atomik.
     *
     * @throws InvalidArgumentException
     */
    public function execute(KasMasukContext $context): KasTransaction
    {
        if ($context->amount <= 0) {
            throw new InvalidArgumentException('Nominal kas masuk harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($context) {
            /** @var KasAccount $account */
            $account = KasAccount::query()
                ->where('id', $context->kasAccountId)
                ->lockForUpdate()
                ->firstOrFail();

            $date = $context->transactionDate ? Carbon::parse($context->transactionDate) : Carbon::today();
            $transactionNumber = $this->generateTransactionNumber($account->branch_id, $date);

            $kasTransaction = KasTransaction::create([
                'transaction_number' => $transactionNumber,
                'kas_account_id' => $account->id,
                'branch_id' => $context->branchId,
                'transaction_type' => KasTransactionType::Masuk,
                'payment_method' => $context->paymentMethod,
                'amount' => $context->amount,
                'customer_id' => $context->customerId,
                'user_id' => $context->userId,
                'reference_number' => $context->referenceNumber,
                'description' => $context->description,
                'transaction_date' => $date->toDateString(),
                'snapshot_payload' => $context->snapshotPayload ?: null,
            ]);

            // Simpan alokasi baris faktur jika ada
            $allocatedToAr = 0.0;
            foreach ($context->lines as $line) {
                $allocationAmount = (float) ($line['allocation_amount'] ?? 0);
                KasTransactionLine::create([
                    'kas_transaction_id' => $kasTransaction->id,
                    'reference_type' => $line['reference_type'] ?? null,
                    'reference_id' => isset($line['reference_id']) ? (int) $line['reference_id'] : null,
                    'allocation_amount' => $allocationAmount,
                    'notes' => $line['notes'] ?? null,
                ]);

                if (($line['reference_type'] ?? '') === 'sales_invoices') {
                    $allocatedToAr += $allocationAmount;
                }
            }

            // Tambah saldo akun kas secara atomik
            $account->increment('current_balance', $context->amount);

            // Pulihkan plafon kredit konsumen jika terdapat pelunasan piutang (AR)
            if ($context->customerId !== null) {
                $creditToRelease = $allocatedToAr > 0 ? $allocatedToAr : $context->amount;
                $customer = Customer::find($context->customerId);
                if ($customer instanceof Customer && (float) $customer->current_credit_used > 0) {
                    $this->customerCreditGuard->releaseCredit($customer, $creditToRelease);
                }
            }

            return $kasTransaction->fresh(['lines', 'kasAccount', 'customer']);
        });
    }

    /**
     * Generate format nomor transaksi: KM/{BRANCH_ID}/{Ymd}/{SEQ}
     */
    private function generateTransactionNumber(int $branchId, Carbon $date): string
    {
        $prefix = sprintf('KM-%03d-%s-', $branchId, $date->format('Ymd'));

        $lastSeq = KasTransaction::query()
            ->where('transaction_number', 'LIKE', "{$prefix}%")
            ->count();

        return sprintf('%s%04d', $prefix, $lastSeq + 1);
    }
}
