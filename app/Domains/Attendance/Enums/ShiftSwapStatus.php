<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Enums;

/**
 * Status Persetujuan Tukar Shift Antar Karyawan (Dua Arah).
 */
enum ShiftSwapStatus: string
{
    case PendingPeer = 'pending_peer';
    case PeerRejected = 'peer_rejected';
    case PendingSupervisor = 'pending_supervisor';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * Dapatkan label human-readable dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingPeer => 'Menunggu Rekan Kerja',
            self::PeerRejected => 'Ditolak Rekan Kerja',
            self::PendingSupervisor => 'Menunggu Supervisor',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak Supervisor',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Dapatkan class badge untuk tampilan UI (Tailwind CSS).
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingPeer => 'bg-amber-100 text-amber-800 border-amber-300',
            self::PeerRejected => 'bg-rose-100 text-rose-800 border-rose-300',
            self::PendingSupervisor => 'bg-sky-100 text-sky-800 border-sky-300',
            self::Approved => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::Rejected => 'bg-red-100 text-red-800 border-red-300',
            self::Cancelled => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }
}
