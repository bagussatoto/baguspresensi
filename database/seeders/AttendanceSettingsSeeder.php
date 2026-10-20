<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Attendance\Models\AttendanceSetting;
use Illuminate\Database\Seeder;

class AttendanceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds for attendance enterprise settings.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'attendance.leave.quota_exhausted_policy',
                'value' => 'strict_block',
                'type' => 'string',
                'group' => 'leave',
                'label' => 'Kebijakan Saldo Cuti Habis',
                'description' => 'Tindakan sistem ketika pengajuan cuti melebihi saldo tahunan (strict_block / admin_discretion).',
                'options' => ['strict_block', 'admin_discretion'],
            ],
            [
                'key' => 'attendance.leave.sick_attachment_policy',
                'value' => 'strict_always',
                'type' => 'string',
                'group' => 'leave',
                'label' => 'Kewajiban Lampiran Surat Dokter',
                'description' => 'Aturan unggah surat dokter saat izin sakit (strict_always, grace_1_day, fully_optional).',
                'options' => ['strict_always', 'grace_1_day', 'fully_optional'],
            ],
            [
                'key' => 'attendance.approval.rejection_note_mandatory',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'approval',
                'label' => 'Wajib Catatan Penolakan Approval',
                'description' => 'Mengharuskan admin / supervisor mengisi justifikasi saat menolak permohonan presensi/cuti.',
                'options' => null,
            ],
            [
                'key' => 'employee.nik.standardization_mode',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'employee',
                'label' => 'Standardisasi Huruf Kapital Kode Karyawan',
                'description' => 'Otomatis melakukan konversi kode karyawan/NIK ke huruf kapital (uppercase).',
                'options' => null,
            ],
            [
                'key' => 'audit.immutability_worm_guard',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'audit',
                'label' => 'Proteksi WORM Audit Trail (Anti-Tamper)',
                'description' => 'Melarang pembaruan (update) dan penghapusan (delete) fisik pada seluruh rekaman audit logs.',
                'options' => null,
            ],
            [
                'key' => 'api.rate_limit_per_minute',
                'value' => '60',
                'type' => 'integer',
                'group' => 'api',
                'label' => 'Batas Kecepatan Akses RESTful API (per Menit)',
                'description' => 'Jumlah maksimal request API presensi per IP address dalam 1 menit.',
                'options' => null,
            ],
            [
                'key' => 'attendance.geofence.default_radius_meters',
                'value' => '100',
                'type' => 'integer',
                'group' => 'geofence',
                'label' => 'Radius Toleransi Geofence Default (Meter)',
                'description' => 'Jarak radius toleransi presensi di luar koordinat titik pusat kantor cabang.',
                'options' => null,
            ],
            [
                'key' => 'attendance.shift.grace_period_minutes',
                'value' => '15',
                'type' => 'integer',
                'group' => 'shift',
                'label' => 'Toleransi Keterlambatan Grace Period (Menit)',
                'description' => 'Waktu toleransi keterlambatan setelah jam mulai shift tanpa penalti.',
                'options' => null,
            ],
        ];

        foreach ($settings as $item) {
            AttendanceSetting::updateOrCreate(
                ['key' => $item['key']],
                $item
            );
        }
    }
}
