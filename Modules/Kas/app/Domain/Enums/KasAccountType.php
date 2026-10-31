<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Enums;

enum KasAccountType: string
{
    case KasirToko = 'kasir_toko';
    case KasOperasional = 'kas_operasional';
    case Bank = 'bank';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::KasirToko => 'Kasir Toko / Laci Kas',
            self::KasOperasional => 'Kas Operasional Kantor',
            self::Bank => 'Rekening Bank',
        };
    }
}
