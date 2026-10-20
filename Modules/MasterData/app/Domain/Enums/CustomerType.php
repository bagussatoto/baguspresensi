<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Enums;

enum CustomerType: string
{
    case RETAIL = 'retail';
    case GROSIR = 'grosir';
    case RESELLER = 'reseller';
    case PROYEK = 'proyek';

    public function label(): string
    {
        return match ($this) {
            self::RETAIL => 'Retail / Eceran',
            self::GROSIR => 'Grosir / Distributor',
            self::RESELLER => 'Reseller',
            self::PROYEK => 'Proyek / B2B Tender',
        };
    }
}
