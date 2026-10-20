<?php

namespace Tests\Feature;

use App\Models\SystemMenu;
use Database\Seeders\SystemMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_menu_seeder_seeds_six_portal_links(): void
    {
        $this->seed(SystemMenuSeeder::class);

        $this->assertDatabaseCount('system_menus', 6);

        $this->assertDatabaseHas('system_menus', [
            'code' => 'presensi2_beranda',
            'url' => 'http://192.168.5.10:8086/bagus/presensi2',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('system_menus', [
            'code' => 'presensi2_stok',
            'url' => 'http://192.168.5.10:8086/bagus/presensi2/stok',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('system_menus', [
            'code' => 'presensi2_penjualan',
            'url' => 'http://192.168.5.10:8086/bagus/presensi2/penjualan',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('system_menus', [
            'code' => 'presensi2_rekap',
            'url' => 'http://192.168.5.10:8086/bagus/presensi2/rekap',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('system_menus', [
            'code' => 'presensi2_riwayat',
            'url' => 'http://192.168.5.10:8086/bagus/presensi2/riwayat',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('system_menus', [
            'code' => 'presensi2_login',
            'url' => 'http://192.168.5.10:8086/bagus/presensi2/login',
            'is_active' => true,
        ]);
    }

    public function test_get_active_by_group_returns_sorted_menus(): void
    {
        $this->seed(SystemMenuSeeder::class);

        $menus = SystemMenu::getActiveByGroup('presensi2');

        $this->assertCount(6, $menus);
        $this->assertEquals('presensi2_beranda', $menus->first()->code);
        $this->assertEquals('presensi2_login', $menus->last()->code);
    }
}
