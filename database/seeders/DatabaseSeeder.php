<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\MasterData\Database\Seeders\InternetCafeSeeder;
use Modules\MasterData\Database\Seeders\MasterDataSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MasterDataSeeder::class);
        $this->call(InternetCafeSeeder::class);
        $this->call(AttendanceMasterSeeder::class);
        $this->call(SystemMenuSeeder::class);
    }
}
