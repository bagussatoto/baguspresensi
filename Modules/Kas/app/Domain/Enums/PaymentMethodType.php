<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Enums;

enum PaymentMethodType: string
{
    case Tunai = 'cash';
    case BankTransfer = 'bank_transfer';
    case Qris = 'qris';
    case Edc = 'edc';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai (Cash)',
            self::BankTransfer => 'Transfer Bank',
            self::Qris => 'QRIS',
            self::Edc => 'Debit / Kartu Kredit EDC',
        };
    }
}
