<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Partial = 'partial';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft Pesanan',
            self::Approved => 'Disetujui / Menunggu Pengiriman',
            self::Partial => 'Diterima Sebagian',
            self::Closed => 'Selesai Lengkap (Closed)',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
