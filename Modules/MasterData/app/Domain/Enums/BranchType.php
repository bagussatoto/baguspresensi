<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Enums;

/**
 * Tipe Entitas Jaringan Cabang dalam Arsitektur MDM ERP Hub-and-Spoke.
 */
enum BranchType: string
{
    case Hq = 'HQ';
    case Dc = 'DC';
    case Store = 'STORE';

    public function label(): string
    {
        return match ($this) {
            self::Hq => 'Kantor Pusat (HQ)',
            self::Dc => 'Pusat Distribusi Logistik (DC)',
            self::Store => 'Toko / Outlet Kasir Ritel (Store)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Hq => 'badge-purple',
            self::Dc => 'badge-blue',
            self::Store => 'badge-emerald',
        };
    }
}
