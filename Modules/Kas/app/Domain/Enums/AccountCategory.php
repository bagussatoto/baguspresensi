<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Enums;

enum AccountCategory: string
{
    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case REVENUE = 'revenue';
    case COGS = 'cogs';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::ASSET => 'Aset / Aktiva (1-XXXXX)',
            self::LIABILITY => 'Kewajiban / Hutang (2-XXXXX)',
            self::EQUITY => 'Ekuitas / Modal (3-XXXXX)',
            self::REVENUE => 'Pendapatan / Omzet (4-XXXXX)',
            self::COGS => 'Harga Pokok Penjualan (5-XXXXX)',
            self::EXPENSE => 'Beban Operasional (6-XXXXX)',
        };
    }

    public function defaultNormalBalance(): NormalBalance
    {
        return match ($this) {
            self::ASSET, self::COGS, self::EXPENSE => NormalBalance::DEBIT,
            self::LIABILITY, self::EQUITY, self::REVENUE => NormalBalance::CREDIT,
        };
    }

    public function prefixCode(): string
    {
        return match ($this) {
            self::ASSET => '1',
            self::LIABILITY => '2',
            self::EQUITY => '3',
            self::REVENUE => '4',
            self::COGS => '5',
            self::EXPENSE => '6',
        };
    }
}
