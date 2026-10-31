<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Enums;

enum SalesOrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Shipped = 'shipped';
    case Invoiced = 'invoiced';
    case Cancelled = 'cancelled';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft Pesanan',
            self::Confirmed => 'Terkonfirmasi (Disetujui)',
            self::Shipped => 'Dikirim (Surat Jalan Terbit)',
            self::Invoiced => 'Faktur Tagihan Terbit',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
