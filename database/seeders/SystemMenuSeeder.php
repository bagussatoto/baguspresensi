<?php

namespace Database\Seeders;

use App\Models\SystemMenu;
use Illuminate\Database\Seeder;

class SystemMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'group' => 'presensi2',
                'code' => 'presensi2_beranda',
                'name' => 'Beranda Presensi',
                'url' => 'http://192.168.5.10:8086/bagus/presensi2',
                'route_name' => 'presensi2.index',
                'icon' => '🏠',
                'sort_order' => 1,
                'is_active' => true,
                'target' => '_self',
                'description' => 'Halaman Beranda Presensi Karyawan MGK (Clock-in, Shift Aktif & Roster Harian)',
            ],
            [
                'group' => 'presensi2',
                'code' => 'presensi2_stok',
                'name' => 'Stok Barang & Serah Terima',
                'url' => 'http://192.168.5.10:8086/bagus/presensi2/stok',
                'route_name' => 'presensi2.stok',
                'icon' => '📦',
                'sort_order' => 2,
                'is_active' => true,
                'target' => '_self',
                'description' => 'Audit Stok Kertas Print (A4 & F4), Minuman, Makanan, Kelayakan Hardware, dan Verifikasi Kas Billing Shift Sebelumnya',
            ],
            [
                'group' => 'presensi2',
                'code' => 'presensi2_penjualan',
                'name' => 'Penjualan & Billing Shift',
                'url' => 'http://192.168.5.10:8086/bagus/presensi2/penjualan',
                'route_name' => 'presensi2.penjualan',
                'icon' => '🛒',
                'sort_order' => 3,
                'is_active' => true,
                'target' => '_self',
                'description' => 'Quick POS Kasir F&B, Pencatatan Otomatis Billing Internet PC Warnet, Permintaan Stok dan Selesai Rekap Shift',
            ],
            [
                'group' => 'presensi2',
                'code' => 'presensi2_rekap',
                'name' => 'Rekap Penjualan & Shift',
                'url' => 'http://192.168.5.10:8086/bagus/presensi2/rekap',
                'route_name' => 'presensi2.rekap',
                'icon' => '📊',
                'sort_order' => 4,
                'is_active' => true,
                'target' => '_self',
                'description' => 'Rekapitulasi Penjualan Shift, Omset F&B, Billing Internet, Sisa Stok Kertas, dan Riwayat Penutupan Kasir',
            ],
            [
                'group' => 'presensi2',
                'code' => 'presensi2_riwayat',
                'name' => 'Riwayat Presensi',
                'url' => 'http://192.168.5.10:8086/bagus/presensi2/riwayat',
                'route_name' => 'presensi2.riwayat',
                'icon' => '📅',
                'sort_order' => 5,
                'is_active' => true,
                'target' => '_self',
                'description' => 'Riwayat Log Presensi Masuk & Pulang, Durasi Kerja, Status Kehadiran, dan Lokasi Geofencing',
            ],
            [
                'group' => 'presensi2',
                'code' => 'presensi2_login',
                'name' => 'Login Presensi & Admin',
                'url' => 'http://192.168.5.10:8086/bagus/presensi2/login',
                'route_name' => 'presensi2.login',
                'icon' => '🔐',
                'sort_order' => 6,
                'is_active' => true,
                'target' => '_self',
                'description' => 'Portal Autentikasi Karyawan & Admin (Pilihan Nama Karyawan, Verifikasi PIN 6-Digit & Password)',
            ],
        ];

        foreach ($menus as $menu) {
            SystemMenu::updateOrCreate(
                ['code' => $menu['code']],
                $menu
            );
        }
    }
}
