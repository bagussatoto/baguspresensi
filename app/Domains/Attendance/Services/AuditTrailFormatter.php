<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use Carbon\Carbon;

class AuditTrailFormatter
{
    /**
     * Kamus terjemahan kunci atribut (Key Dictionary) ke Bahasa Indonesia & Bahasa Inggris Baku.
     *
     * @var array<string, array{id: string, en: string}>
     */
    protected static array $fieldDictionary = [
        'employee_id' => ['id' => 'ID Karyawan', 'en' => 'Employee ID'],
        'employee_name' => ['id' => 'Nama Karyawan', 'en' => 'Employee Name'],
        'employee_code' => ['id' => 'Kode Karyawan', 'en' => 'Employee Code'],
        'nik' => ['id' => 'Nomor Induk Kependudukan (NIK)', 'en' => 'National ID (NIK)'],
        'name' => ['id' => 'Nama', 'en' => 'Name'],
        'email' => ['id' => 'Alamat Email', 'en' => 'Email Address'],
        'phone' => ['id' => 'Nomor Telepon / WhatsApp', 'en' => 'Phone / WhatsApp Number'],
        'role' => ['id' => 'Peran / Hak Akses', 'en' => 'Role / Access Rights'],
        'branch_id' => ['id' => 'ID Cabang', 'en' => 'Branch ID'],
        'branch_name' => ['id' => 'Nama Cabang', 'en' => 'Branch Name'],
        'shift_id' => ['id' => 'ID Shift', 'en' => 'Shift ID'],
        'shift_name' => ['id' => 'Nama Shift', 'en' => 'Shift Name'],
        'work_date' => ['id' => 'Tanggal Dinas Kerja', 'en' => 'Work Date'],
        'clock_in_at' => ['id' => 'Waktu Masuk (Clock-In)', 'en' => 'Clock-In Time'],
        'clock_out_at' => ['id' => 'Waktu Pulang (Clock-Out)', 'en' => 'Clock-Out Time'],
        'is_active' => ['id' => 'Status Keaktifan', 'en' => 'Active Status'],
        'is_locked' => ['id' => 'Status Penguncian', 'en' => 'Lock Status'],
        'locked_at' => ['id' => 'Waktu Penguncian', 'en' => 'Lock Time'],
        'status' => ['id' => 'Status', 'en' => 'Status'],
        'reason' => ['id' => 'Alasan / Justifikasi', 'en' => 'Reason / Justification'],
        'notes' => ['id' => 'Catatan Tambahan', 'en' => 'Additional Notes'],
        'password' => ['id' => 'Kata Sandi', 'en' => 'Password'],
        'pin' => ['id' => 'Kode PIN Keamanan', 'en' => 'Security PIN'],
        'ip_address' => ['id' => 'Alamat IP Pelaku', 'en' => 'Actor IP Address'],
        'user_agent' => ['id' => 'Perangkat / Browser Pelaku', 'en' => 'User Agent / Device'],

        // Kasir POS & Serah Terima Shift
        'handover_amount' => ['id' => 'Jumlah Uang Serah Terima', 'en' => 'Handover Cash Amount'],
        'retained_drawer_cash' => ['id' => 'Modal Kas Ditinggalkan di Laci', 'en' => 'Retained Drawer Cash'],
        'handover_recipient' => ['id' => 'Penerima Serah Terima', 'en' => 'Handover Recipient'],
        'handover_status' => ['id' => 'Status Serah Terima', 'en' => 'Handover Status'],
        'handover_at' => ['id' => 'Waktu Serah Terima', 'en' => 'Handover Time'],
        'handover_by' => ['id' => 'Diserahterimakan Oleh', 'en' => 'Handed Over By'],
        'handover_notes' => ['id' => 'Catatan Serah Terima', 'en' => 'Handover Notes'],
        'deposit_method' => ['id' => 'Metode Setoran Kas', 'en' => 'Deposit Method'],
        'deposit_recipient' => ['id' => 'Penerima Setoran Kas', 'en' => 'Deposit Recipient'],
        'deposited_amount' => ['id' => 'Jumlah Uang Disetor', 'en' => 'Deposited Cash Amount'],
        'deposit_status' => ['id' => 'Status Setoran', 'en' => 'Deposit Status'],
        'deposit_proof' => ['id' => 'Tautan / Bukti Setoran', 'en' => 'Deposit Proof Attachment'],
        'deposit_notes' => ['id' => 'Catatan Setoran', 'en' => 'Deposit Notes'],
        'cash_in_drawer' => ['id' => 'Uang Fisik Kasir di Laci', 'en' => 'Physical Drawer Cash'],
        'system_expected_cash' => ['id' => 'Ekspektasi Kas Sistem', 'en' => 'System Expected Cash'],
        'blind_cash_count' => ['id' => 'Hitungan Fisik Uang Kasir', 'en' => 'Blind Cash Count'],
        'cash_difference' => ['id' => 'Selisih Kasir', 'en' => 'Cash Difference'],

        // Penjualan, Billing & Pembatalan (Void)
        'invoice_number' => ['id' => 'Nomor Faktur / Nota Kasir', 'en' => 'Invoice / Receipt Number'],
        'void_by_id' => ['id' => 'ID Otorisator Pembatalan', 'en' => 'Void Authorizer ID'],
        'void_by_name' => ['id' => 'Diotorisasi Pembatalan Oleh', 'en' => 'Void Authorized By'],
        'void_reason' => ['id' => 'Alasan Pembatalan (Void)', 'en' => 'Void Cancellation Reason'],
        'void_at' => ['id' => 'Waktu Pembatalan (Void)', 'en' => 'Void Timestamp'],
        'cashier_id' => ['id' => 'ID Kasir Bertugas', 'en' => 'On-Duty Cashier ID'],
        'gross_amount' => ['id' => 'Total Bruto Penjualan', 'en' => 'Gross Sales Amount'],
        'discount_amount' => ['id' => 'Jumlah Potongan Diskon', 'en' => 'Discount Amount'],
        'tax_amount' => ['id' => 'Pajak Transaksi (PPN)', 'en' => 'Tax Amount (VAT)'],
        'net_amount' => ['id' => 'Total Netto Penjualan', 'en' => 'Net Sales Amount'],
        'total_fnb' => ['id' => 'Total Penjualan F&B', 'en' => 'Total F&B Sales'],
        'total_billing' => ['id' => 'Total Billing Internet', 'en' => 'Total Internet Billing'],
        'payment_method' => ['id' => 'Metode Pembayaran', 'en' => 'Payment Method'],
        'payment_status' => ['id' => 'Status Pembayaran', 'en' => 'Payment Status'],

        // Potongan Billing & Kertas Print
        'billing_adjustment_amount' => ['id' => 'Potongan Billing (Mati Listrik)', 'en' => 'Billing Adjustment Amount'],
        'billing_blackout_minutes' => ['id' => 'Durasi Mati Listrik (Menit)', 'en' => 'Blackout Duration (Minutes)'],
        'billing_blackout_pcs' => ['id' => 'Jumlah Unit PC Terdampak', 'en' => 'Impacted PC Stations Count'],
        'billing_adjusted_by' => ['id' => 'Disesuaikan Oleh', 'en' => 'Adjusted By'],
        'print_adjustment_pages' => ['id' => 'Potongan Halaman Kertas Rusak', 'en' => 'Print Defect Pages Adjusted'],
        'print_adjustment_amount' => ['id' => 'Nominal Potongan Kertas Rusak', 'en' => 'Print Defect Amount Adjusted'],
        'print_pages' => ['id' => 'Jumlah Lembar Cetak', 'en' => 'Printed Pages Count'],

        // Autentikasi & Keamanan (ISO 27001)
        'login_at' => ['id' => 'Waktu Login', 'en' => 'Login Timestamp'],
        'login_url' => ['id' => 'Halaman Login Diakses', 'en' => 'Login URL Accessed'],
        'lockout_reason' => ['id' => 'Penyebab Akun Terkunci', 'en' => 'Lockout Reason'],
        'failed_attempts' => ['id' => 'Jumlah Percobaan Sandi Salah', 'en' => 'Failed Password Attempts'],
        'unlocked_by' => ['id' => 'Dibuka Kunci Oleh', 'en' => 'Unlocked By'],
        'unlocked_at' => ['id' => 'Waktu Buka Kunci', 'en' => 'Unlock Timestamp'],

        // Manajemen Stok & Pergudangan
        'grand_total' => ['id' => 'Total Akhir / Tagihan', 'en' => 'Grand Total Amount'],
        'paid_amount' => ['id' => 'Jumlah Terbayar', 'en' => 'Paid Amount'],
        'stock_before' => ['id' => 'Stok Awal', 'en' => 'Stock Before'],
        'stock_after' => ['id' => 'Stok Akhir', 'en' => 'Stock After'],
        'current_stock' => ['id' => 'Stok Fisik Saat Ini', 'en' => 'Current Stock Quantity'],
        'reorder_point' => ['id' => 'Batas Minimum Pesan Ulang (ROP)', 'en' => 'Reorder Point (ROP)'],
        'is_sales_locked' => ['id' => 'Status Kunci Penjualan', 'en' => 'Sales Lock Status'],
        'cash_balance' => ['id' => 'Saldo Kas Fisik', 'en' => 'Physical Cash Balance'],

        // Jam Kerja, Izin & Lembur
        'in_time' => ['id' => 'Jam Masuk', 'en' => 'Check-In Time'],
        'out_time' => ['id' => 'Jam Keluar', 'en' => 'Check-Out Time'],
        'start_time' => ['id' => 'Waktu Mulai', 'en' => 'Start Time'],
        'end_time' => ['id' => 'Waktu Selesai', 'en' => 'End Time'],
        'total_hours' => ['id' => 'Total Jam Kerja', 'en' => 'Total Working Hours'],
        'approval_status' => ['id' => 'Status Persetujuan', 'en' => 'Approval Status'],
        'approved_by' => ['id' => 'Disetujui Oleh', 'en' => 'Approved By'],
        'approved_at' => ['id' => 'Waktu Persetujuan', 'en' => 'Approval Timestamp'],
        'rejected_by' => ['id' => 'Ditolak Oleh', 'en' => 'Rejected By'],
        'rejected_at' => ['id' => 'Waktu Penolakan', 'en' => 'Rejection Timestamp'],
        'leave_type' => ['id' => 'Jenis Cuti / Izin', 'en' => 'Leave Type'],
        'overtime_hours' => ['id' => 'Jumlah Jam Lembur', 'en' => 'Overtime Hours'],
        'overtime_amount' => ['id' => 'Nominal Upah Lembur', 'en' => 'Overtime Pay Amount'],
        'late_minutes' => ['id' => 'Keterlambatan (Menit)', 'en' => 'Late Duration (Minutes)'],
        'early_leave_minutes' => ['id' => 'Pulang Cepat (Menit)', 'en' => 'Early Leave (Minutes)'],
    ];

    /**
     * Konversi payload nilai mentah (array) menjadi daftar berpasangan label-nilai human-readable.
     *
     * @param  array<string, mixed>|null  $payload
     * @param  string  $lang  'id' atau 'en'
     * @return array<int, array{key: string, label: string, value: string, is_empty: bool}>
     */
    public static function formatPayload(?array $payload, string $lang = 'id'): array
    {
        return self::formatEntries($payload, $lang);
    }

    /**
     * Konversi payload menjadi string terstruktur satu baris dalam bahasa baku.
     */
    public static function formatToString(?array $payload, string $lang = 'id'): string
    {
        $entries = self::formatEntries($payload, $lang);
        if (empty($entries)) {
            return $lang === 'id' ? 'Tidak Ada' : 'None';
        }

        $parts = [];
        foreach ($entries as $e) {
            $parts[] = $e['label'].': '.$e['value'];
        }

        return implode(' • ', $parts);
    }

    /**
     * Konversi payload nilai mentah (array) menjadi daftar berpasangan label-nilai human-readable.
     *
     * @param  array<string, mixed>|null  $payload
     * @param  string  $lang  'id' atau 'en'
     * @return array<int, array{key: string, label: string, value: string, is_empty: bool}>
     */
    public static function formatEntries(?array $payload, string $lang = 'id'): array
    {
        if (empty($payload)) {
            return [];
        }

        $entries = [];

        foreach ($payload as $key => $rawVal) {
            $keyStr = (string) $key;
            $label = self::translateKey($keyStr, $lang);
            $formattedValue = self::formatValue($keyStr, $rawVal, $lang);

            $entries[] = [
                'key' => $keyStr,
                'label' => $label,
                'value' => $formattedValue,
                'is_empty' => ($rawVal === null || $rawVal === ''),
            ];
        }

        return $entries;
    }

    /**
     * Terjemahkan nama key teknis database menjadi istilah baku.
     */
    public static function translateKey(string $key, string $lang = 'id'): string
    {
        $normalized = strtolower(trim($key));

        if (isset(self::$fieldDictionary[$normalized][$lang])) {
            return self::$fieldDictionary[$normalized][$lang];
        }

        // Fallback: ubah snake_case menjadi Title Case yang rapi
        $titleCase = ucwords(str_replace('_', ' ', $normalized));

        if ($lang === 'id') {
            // Kamus kata per kata sederhana untuk fallback
            $wordMap = [
                'Amount' => 'Jumlah / Nominal',
                'Price' => 'Harga',
                'Cost' => 'Biaya',
                'Count' => 'Jumlah',
                'Date' => 'Tanggal',
                'Time' => 'Waktu',
                'Type' => 'Tipe / Jenis',
                'Status' => 'Status',
                'Reason' => 'Alasan',
                'Notes' => 'Catatan',
                'Description' => 'Keterangan',
                'Total' => 'Total',
                'Code' => 'Kode',
                'Name' => 'Nama',
                'User' => 'Pengguna',
            ];

            return strtr($titleCase, $wordMap);
        }

        return $titleCase;
    }

    /**
     * Format nilai individual sesuai tipe data (Mata Uang, Boolean, Waktu, Status, dsb).
     */
    public static function formatValue(string $key, mixed $val, string $lang = 'id'): string
    {
        if ($val === null || $val === '') {
            return $lang === 'id' ? 'Tidak Ada' : 'None';
        }

        if (is_bool($val)) {
            if ($val) {
                return $lang === 'id' ? 'Aktif / Ya' : 'Active / Yes';
            }

            return $lang === 'id' ? 'Nonaktif / Tidak' : 'Inactive / No';
        }

        if (is_array($val)) {
            // Jika array asosiatif rekursif, format ke baris sederhana
            if (array_keys($val) !== range(0, count($val) - 1)) {
                $subItems = [];
                foreach ($val as $subKey => $subVal) {
                    $subItems[] = self::translateKey((string) $subKey, $lang).': '.self::formatValue((string) $subKey, $subVal, $lang);
                }

                return implode('; ', $subItems);
            }

            // Array numerik
            return implode(', ', array_map('strval', $val));
        }

        $strVal = (string) $val;

        // 1. Cek Format Tanggal & Waktu (Datetime / Date)
        if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', $strVal)) {
            try {
                $c = Carbon::parse($strVal);

                return $c->translatedFormat('d/m/Y H:i:s').($lang === 'id' ? ' WIB' : '');
            } catch (\Throwable) {
                // Ignore and continue
            }
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $strVal)) {
            try {
                $c = Carbon::parse($strVal);

                return $c->translatedFormat('d F Y');
            } catch (\Throwable) {
                // Ignore and continue
            }
        }

        // 2. Cek Format Nominal Mata Uang (Currency)
        $lowerKey = strtolower($key);
        $isCurrencyField = str_contains($lowerKey, 'amount')
            || str_contains($lowerKey, 'price')
            || str_contains($lowerKey, 'cost')
            || str_contains($lowerKey, 'revenue')
            || str_contains($lowerKey, 'cash')
            || str_contains($lowerKey, 'billing')
            || str_contains($lowerKey, 'total')
            || str_contains($lowerKey, 'fee');

        if ($isCurrencyField && is_numeric($val) && ! str_contains($lowerKey, 'count') && ! str_contains($lowerKey, 'pages')) {
            $num = (float) $val;

            return 'Rp '.number_format($num, 0, ',', '.');
        }

        // 3. Cek Status Baku Akuntansi / Operasional
        $statusTranslations = [
            'completed' => ['id' => 'Selesai (Completed)', 'en' => 'Completed'],
            'approved' => ['id' => 'Disetujui (Approved)', 'en' => 'Approved'],
            'rejected' => ['id' => 'Ditolak (Rejected)', 'en' => 'Rejected'],
            'pending' => ['id' => 'Menunggu Otorisasi', 'en' => 'Pending Approval'],
            'verified' => ['id' => 'Terverifikasi Resmi', 'en' => 'Verified'],
            'unsubmitted' => ['id' => 'Belum Diserahkan', 'en' => 'Unsubmitted'],
            'paid' => ['id' => 'Lunas (Paid)', 'en' => 'Paid'],
            'unpaid' => ['id' => 'Belum Lunas', 'en' => 'Unpaid'],
            'cancelled' => ['id' => 'Dibatalkan (Void / Cancelled)', 'en' => 'Cancelled'],
            'open' => ['id' => 'Sedang Berjalan / Buka', 'en' => 'Open / In Progress'],
            'closed' => ['id' => 'Ditutup (Closed)', 'en' => 'Closed'],
            'in_transit' => ['id' => 'Dalam Pengiriman (In Transit)', 'en' => 'In Transit'],
            'received' => ['id' => 'Telah Diterima', 'en' => 'Received'],
            'storage' => ['id' => 'Gudang Penyimpanan Permanen', 'en' => 'Storage'],
            'cash' => ['id' => 'Tunai (Cash)', 'en' => 'Cash'],
            'qris' => ['id' => 'QRIS / Dompet Digital', 'en' => 'QRIS / E-Wallet'],
            'bank_transfer' => ['id' => 'Transfer Rekening Bank', 'en' => 'Bank Transfer'],
            'safe_dropbox' => ['id' => 'Brankas / Drop Box Outlet', 'en' => 'Safe / Drop Box'],
            'cash_collector' => ['id' => 'Kolektor Kas / Owner', 'en' => 'Cash Collector / Owner'],
            'next_shift_handover' => ['id' => 'Serah Terima Estafet Kasir', 'en' => 'Next Shift Handover'],
        ];

        $lowerVal = strtolower(trim($strVal));
        if (isset($statusTranslations[$lowerVal][$lang])) {
            return $statusTranslations[$lowerVal][$lang];
        }

        return $strVal;
    }
}
