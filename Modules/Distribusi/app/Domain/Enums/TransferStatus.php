<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Enums;

enum TransferStatus: string
{
    case Draft = 'draft';
    case InTransit = 'in_transit';
    case Received = 'received';
    case Selisih = 'selisih';
    case Cancelled = 'cancelled';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft Mutasi',
            self::InTransit => 'Dalam Perjalanan (In-Transit)',
            self::Received => 'Diterima Lengkap',
            self::Selisih => 'Diterima dengan Selisih',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
