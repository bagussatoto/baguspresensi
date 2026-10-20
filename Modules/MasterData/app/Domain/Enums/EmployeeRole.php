<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Enums;

enum EmployeeRole: string
{
    case SALESMAN = 'salesman';
    case DRIVER = 'driver';
    case KASIR = 'kasir';
    case OTORISATOR = 'otorisator';
    case SUPERVISOR = 'supervisor';
    case OPERATOR = 'operator';

    public function label(): string
    {
        return match ($this) {
            self::SALESMAN => 'Salesman Lapangan',
            self::DRIVER => 'Driver Logistik',
            self::KASIR => 'Kasir Retail / Toko',
            self::OTORISATOR => 'Otorisator Diskon & Kredit',
            self::SUPERVISOR => 'Supervisor / Outlet Head',
            self::OPERATOR => 'Operator Kasir',
        };
    }
}
