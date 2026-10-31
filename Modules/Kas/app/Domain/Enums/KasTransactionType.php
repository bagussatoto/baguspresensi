<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Enums;

enum KasTransactionType: string
{
    case Masuk = 'in';
    case Keluar = 'out';
    case Mutasi = 'mutation';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Masuk => 'Kas Masuk',
            self::Keluar => 'Kas Keluar',
            self::Mutasi => 'Mutasi Kas / Bank',
        };
    }
}
