<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Enums;

enum TaxIdType: string
{
    case NPWP_16 = 'npwp_16';
    case NIK = 'nik';
    case NON_WP = 'non_wp';

    public function label(): string
    {
        return match ($this) {
            self::NPWP_16 => 'NPWP 16 Digit',
            self::NIK => 'NIK KTP (16 Digit)',
            self::NON_WP => 'Non Wajib Pajak',
        };
    }

    /**
     * Validasi nomor identitas pajak sesuai ketentuan DJP Coretax.
     */
    public function isValidNumber(?string $number): bool
    {
        if ($this === self::NON_WP) {
            return true;
        }

        if ($number === null) {
            return false;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $number);

        return strlen($cleaned ?? '') === 16;
    }
}
