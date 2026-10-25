<?php

namespace App\Console\Commands;

use App\Models\EmployeeRegistration;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('registrations:prune-expired {--days=3 : Batas hari kedaluwarsa}')]
#[Description('Tandai antrean pendaftaran karyawan yang melebihi batas waktu sebagai kedaluwarsa (expired)')]
class PruneExpiredRegistrationsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: EmployeeRegistration::EXPIRATION_DAYS);
        $threshold = now()->subDays($days);

        $expiredCount = EmployeeRegistration::where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->update([
                'status' => 'expired',
                'rejection_reason' => "Kedaluwarsa otomatis karena tidak diverifikasi dalam waktu {$days} hari.",
            ]);

        $this->info("Berhasil memperbarui {$expiredCount} pendaftaran yang kedaluwarsa (> {$days} hari).");

        if ($expiredCount > 0) {
            Log::info("Pendaftaran kedaluwarsa diproses otomatis: {$expiredCount} data diubah ke status 'expired'.", [
                'days_threshold' => $days,
            ]);
        }

        return self::SUCCESS;
    }
}
