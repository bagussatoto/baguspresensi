<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Exceptions;

use RuntimeException;

class InsufficientCashBalanceException extends RuntimeException
{
    public function __construct(
        public readonly float $currentBalance,
        public readonly float $requestedAmount,
        public readonly string $accountName = '',
        string $message = 'Saldo kas/bank tidak mencukupi untuk pengeluaran ini.'
    ) {
        $detail = $accountName !== ''
            ? " Akun: {$accountName} (Saldo: Rp ".number_format($currentBalance, 2, ',', '.').', Dibutuhkan: Rp '.number_format($requestedAmount, 2, ',', '.').').'
            : ' (Saldo: Rp '.number_format($currentBalance, 2, ',', '.').', Dibutuhkan: Rp '.number_format($requestedAmount, 2, ',', '.').').';

        parent::__construct($message.$detail);
    }
}
