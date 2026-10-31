<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Enums;

enum PurchaseBillStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Dibayar',
            self::Partial => 'Dibayar Sebagian',
            self::Paid => 'Lunas',
        };
    }
}
