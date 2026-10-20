<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Enums;

/**
 * Status Persetujuan (Approval) untuk Cuti & Klaim Lembur.
 */
enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * Dapatkan label human-readable dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Persetujuan',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Dapatkan class badge untuk tampilan UI.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800 border-amber-300',
            self::Approved => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::Rejected => 'bg-rose-100 text-rose-800 border-rose-300',
        };
    }
}
