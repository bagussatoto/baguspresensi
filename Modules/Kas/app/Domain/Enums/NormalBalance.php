<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Enums;

enum NormalBalance: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::DEBIT => 'Debit (D)',
            self::CREDIT => 'Kredit (K)',
        };
    }
}
