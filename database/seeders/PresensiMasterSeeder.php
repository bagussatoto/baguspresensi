<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PresensiMasterSeeder extends Seeder
{
    /**
     * Run the database seeds for Presensi Master MGK.
     * Alias yang merujuk langsung ke AttendanceMasterSeeder sesuai spesifikasi TASK-1.7.
     */
    public function run(): void
    {
        $this->call(AttendanceMasterSeeder::class);
    }
}
