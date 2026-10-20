<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Enums;

/**
 * Peran Administrator pada Sistem Presensi & ERP Maya Graha Kencana.
 */
enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case BranchAdmin = 'branch_admin';
    case Auditor = 'auditor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator (Pusat)',
            self::BranchAdmin => 'Administrator / Supervisor Cabang',
            self::Auditor => 'Auditor Kepatuhan (Read-Only)',
        };
    }

    public function isAuditor(): bool
    {
        return $this === self::Auditor;
    }

    public function isBranchAdmin(): bool
    {
        return $this === self::BranchAdmin;
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }
}
