<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Enums;

enum ShiftStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Reconciled = 'reconciled';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Sesi Kasir Aktif (Buka)',
            self::Closed => 'Sesi Ditutup (Menunggu Rekonsiliasi)',
            self::Reconciled => 'Terekonsiliasi Lengkap',
        };
    }
}
