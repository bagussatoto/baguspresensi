<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Http\Controllers\Controller;
use App\Models\EmployeeRegistration;
use App\Models\PasswordResetRequest;
use App\Models\ShiftSalesSummary;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\MasterData\Domain\Models\Brand;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Pricelist;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Supplier;

/**
 * Controller Admin Unified ERP Hub (Pusat Kendali ERP Terpadu).
 * Mengintegrasikan seluruh pilar dan modul ERP Maya Graha Kencana ke dalam 1 tampilan terpadu:
 * - URL: /admin/ERP (dan /admin/erp)
 */
class AdminErpHubController extends Controller
{
    /**
     * Tampilkan Portal ERP Terpadu dengan Quick Status & Direktori Modul Interaktif.
     */
    public function index(Request $request): View|JsonResponse
    {
        $today = Carbon::today();
        $profileData = AdminDashboardController::getProfileData($request);

        // 1. STATISTIK LIVE OPERASIONAL & MASTER DATA
        $totalSku = ProductVariant::where('is_active', true)->count();
        $totalCategories = Category::where('is_active', true)->count();
        $totalBrands = Brand::where('is_active', true)->count();
        $totalSuppliers = Supplier::where('is_active', true)->count();
        $totalCustomers = Customer::where('is_active', true)->count();
        $totalPricelists = Pricelist::where('is_active', true)->count();

        // 2. STATISTIK PERSONEL & PRESENSI
        $activeEmployees = Employee::where('is_active', true)->count();
        $todayAttendance = AttendanceRecord::whereDate('work_date', $today)->count();
        $pendingRegistrations = Schema::hasTable('employee_registrations')
            ? EmployeeRegistration::where('status', 'pending')->count()
            : 0;
        $pendingPasswordResets = Schema::hasTable('password_reset_requests')
            ? PasswordResetRequest::where('status', 'pending')->count()
            : 0;

        // 3. STATISTIK PENJUALAN & TRANSAKSI HARI INI
        $todaySalesCount = CafeSale::whereDate('created_at', $today)->count();
        $todaySalesAmount = (float) CafeSale::whereDate('created_at', $today)->sum('net_amount');
        $pendingBillingAdjustments = ShiftSalesSummary::where(function ($q): void {
            $q->where('billing_adjustment_status', 'pending_approval')
                ->orWhere('print_adjustment_status', 'pending_approval');
        })->count();

        // 4. STATISTIK LOGISTIK & STOK
        $totalPhysicalStock = (int) StockInventory::sum('qty_on_hand');
        $pendingStockTransfers = Schema::hasTable('stock_transfer_dispatches')
            ? DB::table('stock_transfer_dispatches')->where('status', '!=', 'received')->count()
            : 0;

        // 5. STATISTIK KEUANGAN & COA
        $coaAccountsCount = Schema::hasTable('chart_of_accounts')
            ? ChartOfAccount::where('is_active', true)->count()
            : 0;
        $totalLiquidAssets = Schema::hasTable('chart_of_accounts')
            ? (float) ChartOfAccount::where('account_category', 'asset')
                ->where('is_header', false)
                ->sum('current_balance')
            : 0.0;

        // 6. DAFTAR PILAR & MODUL LENGKAP ERP
        $erpModules = $this->getErpModulesStructure([
            'totalSku' => $totalSku,
            'totalCategories' => $totalCategories,
            'totalBrands' => $totalBrands,
            'totalSuppliers' => $totalSuppliers,
            'totalCustomers' => $totalCustomers,
            'totalPricelists' => $totalPricelists,
            'activeEmployees' => $activeEmployees,
            'todayAttendance' => $todayAttendance,
            'pendingRegistrations' => $pendingRegistrations,
            'pendingPasswordResets' => $pendingPasswordResets,
            'todaySalesCount' => $todaySalesCount,
            'todaySalesAmount' => $todaySalesAmount,
            'pendingBillingAdjustments' => $pendingBillingAdjustments,
            'totalPhysicalStock' => $totalPhysicalStock,
            'pendingStockTransfers' => $pendingStockTransfers,
            'coaAccountsCount' => $coaAccountsCount,
            'totalLiquidAssets' => $totalLiquidAssets,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'metrics' => [
                    'total_sku' => $totalSku,
                    'active_employees' => $activeEmployees,
                    'today_sales_count' => $todaySalesCount,
                    'today_sales_amount' => $todaySalesAmount,
                    'total_stock' => $totalPhysicalStock,
                    'coa_accounts' => $coaAccountsCount,
                ],
                'pillars' => $erpModules,
            ]);
        }

        return view('admin.erp.index', compact(
            'profileData',
            'totalSku',
            'totalCategories',
            'totalBrands',
            'totalSuppliers',
            'totalCustomers',
            'totalPricelists',
            'activeEmployees',
            'todayAttendance',
            'pendingRegistrations',
            'pendingPasswordResets',
            'todaySalesCount',
            'todaySalesAmount',
            'pendingBillingAdjustments',
            'totalPhysicalStock',
            'pendingStockTransfers',
            'coaAccountsCount',
            'totalLiquidAssets',
            'erpModules'
        ));
    }

    /**
     * Struktur hierarkis 6 Pilar Utama ERP beserta seluruh Modul & Fitur yang pernah dibangun.
     *
     * @param  array<string, mixed>  $stats
     * @return array<string, mixed>
     */
    private function getErpModulesStructure(array $stats): array
    {
        return [
            'master_data' => [
                'pillar_name' => '1. Master Data & Katalog Terpadu',
                'description' => 'Fondasi data utama perusahaan: cabang jaringan (HQ, DC, Store), produk, varian SKU, kategori, brand, rekanan pemasok, konsumen member, dan tiering harga.',
                'icon' => '🗄️',
                'color' => '#38bdf8', // Blue/Cyan
                'modules' => [
                    [
                        'id' => 'mod-cabang',
                        'title' => 'Jaringan Cabang & Distribution Center (DC)',
                        'code' => 'M-BRN',
                        'desc' => 'Arsitektur MDM Hub-and-Spoke: Kantor Pusat (HQ), Distribution Center (DC), dan Toko Ritel (Store).',
                        'url' => route('admin.cabang'),
                        'route_name' => 'admin.cabang',
                        'icon' => '🏢',
                        'stat_badge' => 'MDM Multi-Cabang',
                        'roles' => ['Super Admin', 'Auditor'],
                        'is_featured' => true,
                    ],
                    [
                        'id' => 'mod-cabang-produk',
                        'title' => 'Lokalisasi Rak & ROP Produk Cabang',
                        'code' => 'M-SPOKE',
                        'desc' => 'Pengaturan rak (bin location), batas minimum, dan Reorder Point (ROP) pemicu transfer dari DC per outlet.',
                        'url' => route('admin.cabang.produk'),
                        'route_name' => 'admin.cabang.produk',
                        'icon' => '📍',
                        'stat_badge' => 'ROP & Bin Location',
                        'roles' => ['Super Admin', 'Branch Admin', 'Logistik'],
                    ],
                    [
                        'id' => 'mod-zonasi-harga',
                        'title' => 'Zonasi Harga & HPP Multi-Cabang',
                        'code' => 'M-ZONE',
                        'desc' => 'Daftar harga jual pasar dan estimasi HPP berdasarkan zona geografis atau cabang spesifik.',
                        'url' => route('admin.cabang.zonasi-harga'),
                        'route_name' => 'admin.cabang.zonasi-harga',
                        'icon' => '💲',
                        'stat_badge' => 'Zone Pricing',
                        'roles' => ['Super Admin', 'Finance'],
                    ],
                    [
                        'id' => 'mod-produk',
                        'title' => 'Produk & Varian SKU (The Hub)',
                        'code' => 'M-PRD',
                        'desc' => 'Katalog produk aktif, barcode scanner, varian ukuran/rasa, status jual, dan relasi kategori.',
                        'url' => route('admin.produk'),
                        'route_name' => 'admin.produk',
                        'icon' => '🏷️',
                        'stat_badge' => ($stats['totalSku'] ?? 0).' SKU',
                        'roles' => ['Super Admin', 'Branch Admin', 'Auditor'],
                    ],
                    [
                        'id' => 'mod-kategori',
                        'title' => 'Kategori Produk',
                        'code' => 'M-CAT',
                        'desc' => 'Pengelompokan barang: Makanan, Minuman, ATK, Voucher, dan Aksesoris Komputer.',
                        'url' => route('admin.kategori'),
                        'route_name' => 'admin.kategori',
                        'icon' => '📂',
                        'stat_badge' => ($stats['totalCategories'] ?? 0).' Kategori',
                        'roles' => ['Super Admin', 'Branch Admin'],
                    ],
                    [
                        'id' => 'mod-merk',
                        'title' => 'Merek / Brand',
                        'code' => 'M-BRD',
                        'desc' => 'Klasifikasi principal produsen dan merek dagang resmi barang ritel/F&B.',
                        'url' => route('admin.merk'),
                        'route_name' => 'admin.merk',
                        'icon' => '⭐',
                        'stat_badge' => ($stats['totalBrands'] ?? 0).' Brand',
                        'roles' => ['Super Admin', 'Branch Admin'],
                    ],
                    [
                        'id' => 'mod-supplier',
                        'title' => 'Supplier / Rekanan Vendor',
                        'code' => 'M-SUP',
                        'desc' => 'Mitra penyedia bahan baku dan barang dagang, alamat, kontak PIC, dan termin pembayaran.',
                        'url' => route('admin.supplier'),
                        'route_name' => 'admin.supplier',
                        'icon' => '🏭',
                        'stat_badge' => ($stats['totalSuppliers'] ?? 0).' Mitra',
                        'roles' => ['Super Admin', 'Branch Admin'],
                    ],
                    [
                        'id' => 'mod-konsumen',
                        'title' => 'Konsumen & Member Cafe/Warnet',
                        'code' => 'M-CUST',
                        'desc' => 'Database pelanggan tetap, kartu member, limit kredit piutang, dan saldo deposit internet.',
                        'url' => route('admin.konsumen'),
                        'route_name' => 'admin.konsumen',
                        'icon' => '👥',
                        'stat_badge' => ($stats['totalCustomers'] ?? 0).' Member',
                        'roles' => ['Super Admin', 'Branch Admin'],
                    ],
                    [
                        'id' => 'mod-harga',
                        'title' => 'Daftar Harga & Tiering',
                        'code' => 'M-PRC',
                        'desc' => 'Matriks harga jual bertingkat: harga umum, harga grosir, dan harga khusus member warnet.',
                        'url' => route('admin.harga'),
                        'route_name' => 'admin.harga',
                        'icon' => '🏷️',
                        'stat_badge' => ($stats['totalPricelists'] ?? 0).' Pricelist',
                        'roles' => ['Super Admin', 'Branch Admin'],
                    ],
                ],
            ],

            'pos_sales' => [
                'pillar_name' => '2. Operasional Kasir & Point of Sale (POS)',
                'description' => 'Layanan penjualan kasir cepat, billing komputer warnet, printer kasir, dan penyesuaian selisih shift.',
                'icon' => '🛒',
                'color' => '#10b981', // Emerald
                'modules' => [
                    [
                        'id' => 'mod-pos-desktop',
                        'title' => 'POS Desktop Kasir & Billing',
                        'code' => 'P-POS',
                        'desc' => 'Kasir F&B, penjualan billing warnet otomatis, cetak struk termal, barcode scanner, dan void modal supervisor.',
                        'url' => route('presensi2.penjualan'),
                        'route_name' => 'presensi2.penjualan',
                        'icon' => '💻',
                        'stat_badge' => 'Aktif Realtime',
                        'roles' => ['Kasir', 'Supervisor', 'Admin'],
                        'is_featured' => true,
                    ],
                    [
                        'id' => 'mod-penjualan-admin',
                        'title' => 'Audit Riwayat Penjualan',
                        'code' => 'P-HIST',
                        'desc' => 'Pemeriksaan seluruh nota transaksi, filter status (selesai/void), dan cetak ulang struk konsumen.',
                        'url' => route('admin.penjualan'),
                        'route_name' => 'admin.penjualan',
                        'icon' => '🧾',
                        'stat_badge' => ($stats['todaySalesCount'] ?? 0).' Transaksi Hari Ini',
                        'roles' => ['Super Admin', 'Branch Admin', 'Auditor'],
                    ],
                    [
                        'id' => 'mod-penyesuaian-billing',
                        'title' => 'Persetujuan Koreksi Billing & Print',
                        'code' => 'P-ADJ',
                        'desc' => 'Otorisasi persetujuan selisih billing internet dan printer kasir per shift kerja.',
                        'url' => route('admin.penjualan.penyesuaian-billing'),
                        'route_name' => 'admin.penjualan.penyesuaian-billing',
                        'icon' => '⚡',
                        'stat_badge' => ($stats['pendingBillingAdjustments'] ?? 0).' Pending',
                        'roles' => ['Super Admin', 'Supervisor'],
                    ],
                    [
                        'id' => 'mod-checklist-kasir',
                        'title' => 'Checklist & Opname Kasir Shift',
                        'code' => 'P-CHK',
                        'desc' => 'Pemeriksaan fisik stok etalase kasir saat awal shift dan serah terima akhir shift.',
                        'url' => route('presensi2.stok'),
                        'route_name' => 'presensi2.stok',
                        'icon' => '📋',
                        'stat_badge' => 'Dual Control',
                        'roles' => ['Kasir', 'Supervisor'],
                    ],
                ],
            ],

            'supply_chain' => [
                'pillar_name' => '3. Persediaan, Gudang & Distribusi',
                'description' => 'Monitoring stok fisik, ambang batas minimum, transfer antar-gudang dan penerimaan barang.',
                'icon' => '📦',
                'color' => '#f59e0b', // Amber
                'modules' => [
                    [
                        'id' => 'mod-stok-ketersediaan',
                        'title' => 'Stok Ketersediaan Real-Time',
                        'code' => 'S-STK',
                        'desc' => 'Pantauan kuantitas sisa stok barang per gudang penyimpanan dan outlet MGK.',
                        'url' => route('admin.stok'),
                        'route_name' => 'admin.stok',
                        'icon' => '📊',
                        'stat_badge' => ($stats['totalPhysicalStock'] ?? 0).' Unit Fisik',
                        'roles' => ['Super Admin', 'Branch Admin', 'Auditor'],
                    ],
                    [
                        'id' => 'mod-stok-limit',
                        'title' => 'Stok Limit & Mutasi Ledger',
                        'code' => 'S-LMT',
                        'desc' => 'Setting batas minimum stok (ROP) dan buku besar mutasi riwayat masuk/keluar produk.',
                        'url' => route('admin.stok-limit'),
                        'route_name' => 'admin.stok-limit',
                        'icon' => '⚠️',
                        'stat_badge' => 'Ledger Fisik',
                        'roles' => ['Super Admin', 'Branch Admin'],
                    ],
                    [
                        'id' => 'mod-distribusi-kirim',
                        'title' => 'Penerimaan & Distribusi Antar Gudang',
                        'code' => 'S-DST',
                        'desc' => 'Surat jalan pengiriman transfer stok dari Gudang Pusat ke Cabang/Outlet serta konfirmasi penerimaan.',
                        'url' => route('admin.distribusi'),
                        'route_name' => 'admin.distribusi',
                        'icon' => '🚚',
                        'stat_badge' => ($stats['pendingStockTransfers'] ?? 0).' Menunggu Terima',
                        'roles' => ['Super Admin', 'Logistik', 'Branch Admin'],
                    ],
                ],
            ],

            'human_resource' => [
                'pillar_name' => '4. Sumber Daya Manusia (HRM) & Presensi',
                'description' => 'Absensi geofence, validasi wifi/liveness, penjadwalan roster shift, verifikasi profil, dan tata kelola akun staf.',
                'icon' => '👥',
                'color' => '#8b5cf6', // Violet
                'modules' => [
                    [
                        'id' => 'mod-presensi-portal',
                        'title' => 'Portal Presensi v2 Karyawan',
                        'code' => 'H-PORT',
                        'desc' => 'Self-service kehadiran mobile & desktop: geofence 100m, validasi wifi, foto liveness AI, dan multi-role.',
                        'url' => route('presensi2.index'),
                        'route_name' => 'presensi2.index',
                        'icon' => '📱',
                        'stat_badge' => ($stats['todayAttendance'] ?? 0).' Hadir Hari Ini',
                        'roles' => ['Seluruh Karyawan'],
                        'is_featured' => true,
                    ],
                    [
                        'id' => 'mod-karyawan-master',
                        'title' => 'Data Karyawan & Hak Akses',
                        'code' => 'H-EMP',
                        'desc' => 'Biodata staf, jabatan, outlet penempatan, konfigurasi role (kasir, supervisor, dll), dan PIN operasional.',
                        'url' => route('admin.karyawan'),
                        'route_name' => 'admin.karyawan',
                        'icon' => '👤',
                        'stat_badge' => ($stats['activeEmployees'] ?? 0).' Staf Aktif',
                        'roles' => ['Super Admin', 'Branch Admin'],
                    ],
                    [
                        'id' => 'mod-jadwal-shift',
                        'title' => 'Jadwal Kerja & Roster Shift',
                        'code' => 'H-SCH',
                        'desc' => 'Penjadwalan shift reguler & hybrid, kalender kerja bulanan, dan impor jadwal batch CSV.',
                        'url' => route('admin.jadwal'),
                        'route_name' => 'admin.jadwal',
                        'icon' => '📅',
                        'stat_badge' => 'Shift Pagi/Siang/Malam',
                        'roles' => ['Super Admin', 'Branch Admin'],
                    ],
                    [
                        'id' => 'mod-verifikasi-karyawan',
                        'title' => 'Verifikasi Pendaftaran Karyawan',
                        'code' => 'H-REG',
                        'desc' => 'Persetujuan pendaftaran karyawan baru dengan sistem kontrol ganda (Maker-Checker).',
                        'url' => route('admin.karyawan.verifikasi'),
                        'route_name' => 'admin.karyawan.verifikasi',
                        'icon' => '🛡️',
                        'stat_badge' => ($stats['pendingRegistrations'] ?? 0).' Menunggu Verifikasi',
                        'roles' => ['Super Admin'],
                    ],
                    [
                        'id' => 'mod-reset-pin',
                        'title' => 'Otorisasi Reset Password & PIN',
                        'code' => 'H-RST',
                        'desc' => 'Verifikasi dan pemberian izin reset PIN otorisasi kasir / password karyawan yang terlupa.',
                        'url' => route('admin.karyawan.reset-password'),
                        'route_name' => 'admin.karyawan.reset-password',
                        'icon' => '🔑',
                        'stat_badge' => ($stats['pendingPasswordResets'] ?? 0).' Pending Request',
                        'roles' => ['Super Admin'],
                    ],
                    [
                        'id' => 'mod-unlock-login',
                        'title' => 'Buka Blokir Login (Unlock)',
                        'code' => 'H-UNL',
                        'desc' => 'Persetujuan pembukaan blokir akun karyawan yang terkunci otomatis akibat 3x salah password.',
                        'url' => route('admin.karyawan.unlock'),
                        'route_name' => 'admin.karyawan.unlock',
                        'icon' => '🔓',
                        'stat_badge' => 'Anti Brute Force',
                        'roles' => ['Super Admin'],
                    ],
                    [
                        'id' => 'mod-access-review',
                        'title' => 'Tinjauan Hak Akses (ISO 27001)',
                        'code' => 'H-REV',
                        'desc' => 'Evaluasi kepatuhan hak akses staf berkala & otomatisasi de-provisioning bagi staf non-aktif.',
                        'url' => route('admin.karyawan.access-review'),
                        'route_name' => 'admin.karyawan.access-review',
                        'icon' => '📋',
                        'stat_badge' => 'Audit ISO/IEC A.8.5',
                        'roles' => ['Super Admin', 'Auditor'],
                    ],
                    [
                        'id' => 'mod-rekap-presensi',
                        'title' => 'Rekap Presensi & Audit Log Harian',
                        'code' => 'H-AUD',
                        'desc' => 'Laporan rekapitulasi kehadiran, durasi jam kerja, keterlambatan, dan ekspor CSV audit log.',
                        'url' => route('admin.presensi'),
                        'route_name' => 'admin.presensi',
                        'icon' => '📑',
                        'stat_badge' => 'Daily Audit CSV',
                        'roles' => ['Super Admin', 'Auditor'],
                    ],
                ],
            ],

            'finance_accounting' => [
                'pillar_name' => '5. Keuangan, Kas & Akuntansi (Buku Besar)',
                'description' => 'Sistem akuntansi pembukuan berpasangan (Double-Entry SAK): Jurnal Umum, Buku Besar, Neraca Saldo, Laporan Laba Rugi, dan Neraca Keuangan.',
                'icon' => '💰',
                'color' => '#06b6d4', // Cyan
                'modules' => [
                    [
                        'id' => 'mod-jurnal-umum',
                        'title' => 'Jurnal Umum (General Journal)',
                        'code' => 'F-JRN',
                        'desc' => 'Pencatatan kronologis debit & kredit berpasangan yang otomatis tersinkron dari transaksi POS kasir dan jurnal manual.',
                        'url' => route('admin.akuntansi.jurnal'),
                        'route_name' => 'admin.akuntansi.jurnal',
                        'icon' => '📖',
                        'stat_badge' => 'Double Entry SAK',
                        'roles' => ['Super Admin', 'Finance', 'Auditor'],
                        'is_featured' => true,
                    ],
                    [
                        'id' => 'mod-buku-besar',
                        'title' => 'Buku Besar (General Ledger)',
                        'code' => 'F-GL',
                        'desc' => 'Kartu mutasi individual per akun COA untuk memantau saldo awal, arus kas masuk/keluar, dan saldo akhir berjalan.',
                        'url' => route('admin.akuntansi.buku-besar'),
                        'route_name' => 'admin.akuntansi.buku-besar',
                        'icon' => '📑',
                        'stat_badge' => 'Kartu Akun GL',
                        'roles' => ['Super Admin', 'Finance', 'Auditor'],
                    ],
                    [
                        'id' => 'mod-neraca-saldo',
                        'title' => 'Neraca Saldo (Trial Balance)',
                        'code' => 'F-TB',
                        'desc' => 'Validasi matematis keseimbangan total debit dan total kredit seluruh akun buku besar periode berjalan.',
                        'url' => route('admin.akuntansi.neraca-saldo'),
                        'route_name' => 'admin.akuntansi.neraca-saldo',
                        'icon' => '⚖️',
                        'stat_badge' => 'Keseimbangan SAK',
                        'roles' => ['Super Admin', 'Finance', 'Auditor'],
                    ],
                    [
                        'id' => 'mod-laba-rugi',
                        'title' => 'Laporan Laba Rugi (Income Statement)',
                        'code' => 'F-PL',
                        'desc' => 'Kalkulasi laba kotor dan laba bersih: Pendapatan Usaha dikurangi HPP Barang/Bahan dan Beban Operasional.',
                        'url' => route('admin.akuntansi.laba-rugi'),
                        'route_name' => 'admin.akuntansi.laba-rugi',
                        'icon' => '📈',
                        'stat_badge' => 'Surplus Laba',
                        'roles' => ['Super Admin', 'Owner', 'Auditor'],
                        'is_featured' => true,
                    ],
                    [
                        'id' => 'mod-neraca-keuangan',
                        'title' => 'Neraca Keuangan (Balance Sheet)',
                        'code' => 'F-BS',
                        'desc' => 'Struktur kekayaan perusahaan: Total Aset berbanding Kewajiban Hutang ditambah Ekuitas Modal Pemilik.',
                        'url' => route('admin.akuntansi.neraca'),
                        'route_name' => 'admin.akuntansi.neraca',
                        'icon' => '🏛️',
                        'stat_badge' => 'Aset = Pasiva',
                        'roles' => ['Super Admin', 'Owner', 'Auditor'],
                    ],
                    [
                        'id' => 'mod-pivot-transaksi',
                        'title' => 'Pivot Analisis Transaksi SAK',
                        'code' => 'F-PVT',
                        'desc' => 'Tabel matriks pivot multidimensi data transaksi jurnal SAK berdasarkan akun COA, cabang, periode, dan kategori.',
                        'url' => route('admin.akuntansi.pivot'),
                        'route_name' => 'admin.akuntansi.pivot',
                        'icon' => '📊',
                        'stat_badge' => 'Pivot Matrix SAK',
                        'roles' => ['Super Admin', 'Finance', 'Auditor'],
                        'is_featured' => true,
                    ],
                    [
                        'id' => 'mod-coa-chart',
                        'title' => 'Bagan Akun (Chart of Accounts / COA)',
                        'code' => 'F-COA',
                        'desc' => 'Hierarki bagan akun buku besar 6 kategori SAK: Aset, Kewajiban, Ekuitas, Pendapatan, HPP, dan Beban Operasional.',
                        'url' => route('admin.coa'),
                        'route_name' => 'admin.coa',
                        'icon' => '🏷️',
                        'stat_badge' => ($stats['coaAccountsCount'] ?? 0).' Akun Terdaftar',
                        'roles' => ['Super Admin', 'Auditor', 'Finance'],
                    ],
                    [
                        'id' => 'mod-laporan-keuangan',
                        'title' => 'Laporan Penjualan & Z-Report Shift',
                        'code' => 'F-REP',
                        'desc' => 'Laporan penjualan harian (Z-Report), rekap setoran kas kasir per shift, dan margin F&B.',
                        'url' => route('admin.laporan'),
                        'route_name' => 'admin.laporan',
                        'icon' => '📊',
                        'stat_badge' => 'Z-Report Shift',
                        'roles' => ['Super Admin', 'Auditor'],
                    ],
                ],
            ],

            'security_governance' => [
                'pillar_name' => '6. Keamanan, Tata Kelola & Audit Trail (GRC)',
                'description' => 'Pencatatan seluruh log audit forensik, dual-control Maker-Checker, dan matriks Role-Based Access Control (RBAC).',
                'icon' => '🛡️',
                'color' => '#f43f5e', // Rose
                'modules' => [
                    [
                        'id' => 'mod-audit-logs',
                        'title' => 'Audit Trail & Aktivitas Sistem',
                        'code' => 'G-LOG',
                        'desc' => 'Jejak rekam digital semua mutasi data: create, update, delete, void penjualan, dan modifikasi jadwal.',
                        'url' => route('admin.presensi.audit-logs'),
                        'route_name' => 'admin.presensi.audit-logs',
                        'icon' => '📜',
                        'stat_badge' => 'Immutable Log',
                        'roles' => ['Super Admin', 'Auditor'],
                    ],
                    [
                        'id' => 'mod-rbac-matrix',
                        'title' => 'Matriks Role-Based Access Control (RBAC)',
                        'code' => 'G-RBAC',
                        'desc' => 'Dokumentasi hak akses dua lapis: AdminRole (super_admin, branch_admin, auditor) & EmployeeRole (kasir, supervisor, dll).',
                        'url' => route('admin.karyawan.access-review'),
                        'route_name' => 'admin.karyawan.access-review',
                        'icon' => '🔒',
                        'stat_badge' => '2-Tier RBAC',
                        'roles' => ['Super Admin', 'Auditor'],
                    ],
                ],
            ],
        ];
    }
}
