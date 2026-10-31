<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminAttendanceSettingController;
use App\Http\Controllers\Admin\AdminBrandController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEmployeeController;
use App\Http\Controllers\Admin\AdminPricelistController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSaleController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminStockController;
use App\Http\Controllers\Admin\AdminSupplierController;
use App\Http\Controllers\Admin\AuditLogAdminController;
use App\Http\Controllers\AttendanceWebController;
use App\Http\Controllers\AttendanceWebV2Controller;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Staff\StaffDashboardController;
use Illuminate\Support\Facades\Route;
use Modules\MasterData\Application\Http\Controllers\InternetCafeController;
use Modules\MasterData\Application\Http\Controllers\MasterDataWebController;
use Modules\MasterData\Domain\Models\Employee;

Route::get('/', function () {
    return redirect()->route('presensi2.index');
});
Route::get('/bagus', function () {
    return redirect()->route('presensi2.index');
});
Route::get('/bagus/admin', function () {
    return redirect()->route('admin.index');
});
Route::get('/bagus/admin/data', function () {
    return redirect()->route('admin.data');
});
Route::get('/bagus/admin/distribusi', function () {
    return redirect()->route('admin.distribusi');
});
Route::get('/bagus/admin/stok', function () {
    return redirect()->route('admin.stok');
});
Route::get('/bagus/admin/laporan', function () {
    return redirect()->route('admin.laporan');
});
Route::get('/warnet', [InternetCafeController::class, 'index'])->name('warnet.index');
Route::get('/bagus/warnet', [InternetCafeController::class, 'index'])->name('bagus.warnet');
Route::get('/master-data', [MasterDataWebController::class, 'index'])->name('master-data.index');

// Portal Presensi Karyawan Mobile / PWA (v1 - Existing)
Route::prefix('presensi')->name('presensi.')->group(function () {
    Route::get('/login', [AttendanceWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AttendanceWebController::class, 'login'])->name('login.post');
    Route::match(['get', 'post'], '/logout', [AttendanceWebController::class, 'logout'])->name('logout');

    Route::get('/', [AttendanceWebController::class, 'index'])->name('index');
    Route::get('/scan', [AttendanceWebController::class, 'scan'])->name('scan');
    Route::post('/clock-in', [AttendanceWebController::class, 'submitClockIn'])->name('clock-in');
    Route::post('/clock-out', [AttendanceWebController::class, 'submitClockOut'])->name('clock-out');
    Route::post('/sync-offline', [AttendanceWebController::class, 'syncOffline'])->name('sync-offline');

    Route::get('/izin', [AttendanceWebController::class, 'izin'])->name('izin');
    Route::post('/izin', [AttendanceWebController::class, 'submitLeave'])->name('izin.submit');

    Route::get('/riwayat', [AttendanceWebController::class, 'riwayat'])->name('riwayat');
    Route::get('/profil', [AttendanceWebController::class, 'profile'])->name('profile');

    Route::get('/koreksi', [AttendanceWebController::class, 'koreksi'])->name('koreksi');
    Route::post('/koreksi', [AttendanceWebController::class, 'submitCorrection'])->name('koreksi.submit');

    Route::get('/tukar-shift', [AttendanceWebController::class, 'shiftSwap'])->name('tukar-shift');
    Route::post('/tukar-shift', [AttendanceWebController::class, 'submitShiftSwap'])->name('tukar-shift.submit');
    Route::post('/tukar-shift/{swap}/peer-respond', [AttendanceWebController::class, 'peerRespondShiftSwap'])->name('tukar-shift.peer-respond');
    Route::post('/tukar-shift/{swap}/cancel', [AttendanceWebController::class, 'cancelShiftSwap'])->name('tukar-shift.cancel');

    Route::post('/overtime/request', [AttendanceWebController::class, 'requestOvertime'])->name('overtime.request');
    Route::post('/early-leave/request', [AttendanceWebController::class, 'requestEarlyLeave'])->name('early-leave.request');
});

// Portal Presensi Karyawan v2 (Verifikasi Password / PIN 6-Digit)
Route::prefix('presensi2')->name('presensi2.')->group(function () {
    Route::get('/login', [AttendanceWebV2Controller::class, 'showLogin'])->name('login');
    Route::post('/login', [AttendanceWebV2Controller::class, 'login'])->middleware('throttle:30,1')->name('login.post');
    Route::get('/register', [AttendanceWebV2Controller::class, 'showRegister'])->name('register');
    Route::post('/register/check-availability', [AttendanceWebV2Controller::class, 'checkAvailability'])->middleware('throttle:30,1')->name('register.check');
    Route::post('/register', [AttendanceWebV2Controller::class, 'register'])->middleware('throttle:6,1')->name('register.post');
    Route::get('/lupa-password', [AttendanceWebV2Controller::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/lupa-password', [AttendanceWebV2Controller::class, 'resetPassword'])->middleware('throttle:5,1')->name('forgot-password.post');
    Route::get('/cek-status', [AttendanceWebV2Controller::class, 'showCheckStatus'])->name('check-status');
    Route::post('/cek-status', [AttendanceWebV2Controller::class, 'checkStatusQuery'])->middleware('throttle:20,1')->name('check-status.query');
    Route::get('/cek-status-reset', [AttendanceWebV2Controller::class, 'showCheckResetStatus'])->name('check-reset-status');
    Route::post('/cek-status-reset', [AttendanceWebV2Controller::class, 'checkResetStatusQuery'])->middleware('throttle:20,1')->name('check-reset-status.query');
    Route::match(['get', 'post'], '/logout', [AttendanceWebV2Controller::class, 'logout'])->name('logout');

    Route::get('/', [AttendanceWebV2Controller::class, 'index'])->name('index');
    Route::get('/scan', [AttendanceWebV2Controller::class, 'scan'])->name('scan');
    Route::post('/clock-in', [AttendanceWebV2Controller::class, 'submitClockIn'])->middleware('throttle:10,1')->name('clock-in');
    Route::post('/clock-out', [AttendanceWebV2Controller::class, 'submitClockOut'])->middleware('throttle:10,1')->name('clock-out');
    Route::post('/sync-offline', [AttendanceWebV2Controller::class, 'syncOffline'])->name('sync-offline');
    Route::get('/riwayat', [AttendanceWebV2Controller::class, 'riwayat'])->name('riwayat');
    Route::get('/koreksi', [AttendanceWebV2Controller::class, 'koreksiIndex'])->name('koreksi');
    Route::post('/koreksi', [AttendanceWebV2Controller::class, 'koreksiSubmit'])->name('koreksi.submit');

    // Halaman Stok Barang (Checklist Serah Terima Shift)
    Route::get('/stok', [AttendanceWebV2Controller::class, 'stokIndex'])->name('stok');
    Route::post('/stok', [AttendanceWebV2Controller::class, 'stokStore'])->name('stok.store');

    // Halaman Penjualan (Kasir F&B, Pendapatan Internet, Permintaan Stok, & Check Out Presensi)
    Route::get('/penjualan', [AttendanceWebV2Controller::class, 'penjualanIndex'])->name('penjualan');
    Route::post('/penjualan/transaksi', [AttendanceWebV2Controller::class, 'penjualanStoreTransaction'])->name('penjualan.transaksi');
    Route::match(['get', 'post'], '/penjualan/{sale}/batal', [AttendanceWebV2Controller::class, 'penjualanCancelTransaction'])->name('penjualan.batal');
    Route::post('/penjualan/permintaan-stok', [AttendanceWebV2Controller::class, 'penjualanStoreStockRequest'])->name('penjualan.permintaan-stok');
    Route::post('/penjualan/kunci-billing', [AttendanceWebV2Controller::class, 'penjualanKunciBilling'])->name('penjualan.kunci-billing');
    Route::post('/penjualan/kunci-print', [AttendanceWebV2Controller::class, 'penjualanKunciPrint'])->name('penjualan.kunci-print');
    Route::get('/penjualan/printer/status', [AttendanceWebV2Controller::class, 'getPrinterStatus'])->name('penjualan.printer.status');
    Route::get('/penjualan/printer/agent-script', [AttendanceWebV2Controller::class, 'downloadPrinterAgentScript'])->name('penjualan.printer.agent-script');
    Route::post('/penjualan/printer/manual-sync', [AttendanceWebV2Controller::class, 'manualSyncPrinterData'])->name('penjualan.printer.manual-sync');
    Route::post('/penjualan/selesai', [AttendanceWebV2Controller::class, 'penjualanSelesai'])->name('penjualan.selesai');

    // Halaman Rekap (Rekapitulasi Penjualan, Pendapatan Billing, Audit Stok & Kasir Selesai Shift)
    Route::get('/rekap', [AttendanceWebV2Controller::class, 'rekapIndex'])->name('rekap');
    Route::post('/rekap/setor', [AttendanceWebV2Controller::class, 'rekapStoreSetor'])->name('rekap.setor');

    // Portal Login Khusus Layanan & Presensi Tambahan (GC, Meeting, BO/Training, Ambil Stok)
    Route::get('/layanan/login', [AttendanceWebV2Controller::class, 'showLayananLogin'])->name('layanan.login');
    Route::post('/layanan/login', [AttendanceWebV2Controller::class, 'layananLogin'])->name('layanan.login.post');
    Route::match(['get', 'post'], '/layanan/logout', [AttendanceWebV2Controller::class, 'layananLogout'])->name('layanan.logout');

    // 1. General Cleaning (GC)
    Route::get('/gc', [AttendanceWebV2Controller::class, 'gcIndex'])->name('gc');
    Route::post('/gc', [AttendanceWebV2Controller::class, 'gcStore'])->name('gc.store');
    Route::get('/gc/login', function () {
        return redirect()->route('presensi2.layanan.login', ['service' => 'gc']);
    })->name('gc.login');

    // 2. Meeting
    Route::get('/meeting', [AttendanceWebV2Controller::class, 'meetingIndex'])->name('meeting');
    Route::post('/meeting', [AttendanceWebV2Controller::class, 'meetingStore'])->name('meeting.store');
    Route::get('/meeting/login', function () {
        return redirect()->route('presensi2.layanan.login', ['service' => 'meeting']);
    })->name('meeting.login');

    // 3. Presensi BO / Training
    Route::get('/bo-training', [AttendanceWebV2Controller::class, 'boTrainingIndex'])->name('bo-training');
    Route::post('/bo-training', [AttendanceWebV2Controller::class, 'boTrainingStore'])->name('bo-training.store');
    Route::get('/bo-training/login', function () {
        return redirect()->route('presensi2.layanan.login', ['service' => 'bo-training']);
    })->name('bo-training.login');

    // 4. AMBIL STOK
    Route::get('/ambil-stok', [AttendanceWebV2Controller::class, 'ambilStokIndex'])->name('ambil-stok');
    Route::post('/ambil-stok', [AttendanceWebV2Controller::class, 'ambilStokStore'])->name('ambil-stok.store');
    Route::post('/ambil-stok/{id}/terima', [AttendanceWebV2Controller::class, 'ambilStokReceiveTransfer'])->name('ambil-stok.terima');
    Route::get('/ambil-stok/login', function () {
        return redirect()->route('presensi2.layanan.login', ['service' => 'ambil-stok']);
    })->name('ambil-stok.login');
});

// Route pembatalan penjualan fleksibel (GET & POST) yang langsung masuk ke Admin ERP
Route::match(['get', 'post'], '/bagus/presensi2/penjualan/{sale}/batal', [AttendanceWebV2Controller::class, 'penjualanCancelTransaction']);
Route::match(['get', 'post'], '/admin/penjualan/{sale}/batal', [AttendanceWebV2Controller::class, 'penjualanCancelTransaction'])->name('admin.penjualan.batal');
Route::get('/bagus/presensi2/rekap', [AttendanceWebV2Controller::class, 'rekapIndex']);

// Alias URL alternatif presensi/login2 dan register sesuai kebutuhan user
Route::get('/presensi/login2', [AttendanceWebV2Controller::class, 'showLogin']);
Route::post('/presensi/login2', [AttendanceWebV2Controller::class, 'login'])->middleware('throttle:5,1');
Route::get('/presensi/daftar2', [AttendanceWebV2Controller::class, 'showRegister']);
Route::post('/presensi/daftar2', [AttendanceWebV2Controller::class, 'register'])->middleware('throttle:6,1');
Route::get('/presensi/register', [AttendanceWebV2Controller::class, 'showRegister']);
Route::post('/presensi/register', [AttendanceWebV2Controller::class, 'register'])->middleware('throttle:6,1');
Route::get('/presensi/lupa-password', [AttendanceWebV2Controller::class, 'showForgotPassword']);
Route::post('/presensi/lupa-password', [AttendanceWebV2Controller::class, 'resetPassword'])->middleware('throttle:5,1');

// Alias URL alternatif untuk GC, Meeting, BO/Training, dan Ambil Stok
Route::get('/presensi/gc', [AttendanceWebV2Controller::class, 'gcIndex']);
Route::post('/presensi/gc', [AttendanceWebV2Controller::class, 'gcStore']);
Route::get('/presensi/meeting', [AttendanceWebV2Controller::class, 'meetingIndex']);
Route::post('/presensi/meeting', [AttendanceWebV2Controller::class, 'meetingStore']);
Route::get('/presensi/bo-training', [AttendanceWebV2Controller::class, 'boTrainingIndex']);
Route::post('/presensi/bo-training', [AttendanceWebV2Controller::class, 'boTrainingStore']);
Route::get('/presensi/ambil-stok', [AttendanceWebV2Controller::class, 'ambilStokIndex']);
Route::post('/presensi/ambil-stok', [AttendanceWebV2Controller::class, 'ambilStokStore']);

// Admin ERP Maya Graha Kencana
Route::prefix('admin')->middleware(['web', 'auditor.readonly'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.index');
    Route::get('/data', [AdminDashboardController::class, 'dataIndex'])->name('admin.data');
    Route::get('/distribusi', [AdminDashboardController::class, 'distribusiIndex'])->name('admin.distribusi');
    Route::get('/stok', [AdminDashboardController::class, 'stokIndex'])->name('admin.stok');
    Route::get('/laporan', [AdminDashboardController::class, 'laporanIndex'])->name('admin.laporan');
    Route::post('/distribusi/{id}/terima', [AdminDashboardController::class, 'receiveTransfer'])->name('admin.distribusi.terima');
    Route::get('/merk', [AdminBrandController::class, 'index'])->name('admin.merk');
    Route::post('/merk', [AdminBrandController::class, 'store'])->name('admin.merk.store');
    Route::put('/merk/{brand}', [AdminBrandController::class, 'update'])->name('admin.merk.update');
    Route::post('/merk/{brand}/toggle-status', [AdminBrandController::class, 'toggleStatus'])->name('admin.merk.toggle-status');
    Route::delete('/merk/{brand}', [AdminBrandController::class, 'destroy'])->name('admin.merk.destroy');

    Route::get('/kategori', [AdminCategoryController::class, 'index'])->name('admin.kategori');
    Route::post('/kategori', [AdminCategoryController::class, 'store'])->name('admin.kategori.store');
    Route::put('/kategori/{category}', [AdminCategoryController::class, 'update'])->name('admin.kategori.update');
    Route::post('/kategori/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('admin.kategori.toggle-status');
    Route::delete('/kategori/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.kategori.destroy');

    Route::get('/supplier', [AdminSupplierController::class, 'index'])->name('admin.supplier');
    Route::post('/supplier', [AdminSupplierController::class, 'store'])->name('admin.supplier.store');
    Route::put('/supplier/{supplier}', [AdminSupplierController::class, 'update'])->name('admin.supplier.update');
    Route::post('/supplier/{supplier}/toggle-status', [AdminSupplierController::class, 'toggleStatus'])->name('admin.supplier.toggle-status');
    Route::delete('/supplier/{supplier}', [AdminSupplierController::class, 'destroy'])->name('admin.supplier.destroy');

    // Produk & Varian Katalog ERP
    Route::get('/produk', [AdminProductController::class, 'index'])->name('admin.produk');
    Route::post('/produk', [AdminProductController::class, 'store'])->name('admin.produk.store');
    Route::put('/produk/{product}', [AdminProductController::class, 'update'])->name('admin.produk.update');
    Route::post('/produk/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('admin.produk.toggle-status');
    Route::delete('/produk/{product}', [AdminProductController::class, 'destroy'])->name('admin.produk.destroy');

    // Konsumen & Pelanggan Member ERP
    Route::get('/konsumen', [AdminCustomerController::class, 'index'])->name('admin.konsumen');
    Route::post('/konsumen', [AdminCustomerController::class, 'store'])->name('admin.konsumen.store');
    Route::put('/konsumen/{customer}', [AdminCustomerController::class, 'update'])->name('admin.konsumen.update');
    Route::post('/konsumen/{customer}/toggle-status', [AdminCustomerController::class, 'toggleStatus'])->name('admin.konsumen.toggle-status');
    Route::delete('/konsumen/{customer}', [AdminCustomerController::class, 'destroy'])->name('admin.konsumen.destroy');

    // Stok Limit & Ledger Inventaris Fisik Gudang
    Route::get('/stok-limit', [AdminStockController::class, 'index'])->name('admin.stok-limit');
    Route::put('/stok-limit/{stock}', [AdminStockController::class, 'update'])->name('admin.stok-limit.update');

    // Daftar Harga (Pricelists) & Tiering
    Route::get('/harga', [AdminPricelistController::class, 'index'])->name('admin.harga');
    Route::post('/harga', [AdminPricelistController::class, 'store'])->name('admin.harga.store');
    Route::put('/harga/{pricelist}', [AdminPricelistController::class, 'update'])->name('admin.harga.update');
    Route::post('/harga/{pricelist}/item', [AdminPricelistController::class, 'storeItem'])->name('admin.harga.item');
    Route::delete('/harga/{pricelist}', [AdminPricelistController::class, 'destroy'])->name('admin.harga.destroy');

    Route::get('/jadwal', [AdminScheduleController::class, 'index'])->name('admin.jadwal');
    Route::post('/jadwal', [AdminScheduleController::class, 'store'])->name('admin.jadwal.store');
    Route::post('/jadwal/upload-static', [AdminScheduleController::class, 'uploadStatic'])->name('admin.jadwal.upload-static');
    Route::post('/jadwal/{schedule}/set-active', [AdminScheduleController::class, 'setActive'])->name('admin.jadwal.set-active');
    Route::delete('/jadwal/{schedule}', [AdminScheduleController::class, 'destroy'])->name('admin.jadwal.destroy');

    Route::get('/karyawan', [AdminEmployeeController::class, 'index'])->name('admin.karyawan');
    Route::get('/karyawan/profile', function () {
        return redirect()->to(route('admin.karyawan').'#profile');
    })->name('admin.karyawan.profile');
    Route::get('/karyawan/{employee}', [AdminEmployeeController::class, 'show'])->name('admin.karyawan.show');
    Route::get('/karyawan/{employee}/profile', function (Employee $employee) {
        return redirect()->to(route('admin.karyawan').'#profile-'.$employee->id);
    });
    Route::post('/karyawan', [AdminEmployeeController::class, 'store'])->name('admin.karyawan.store');
    Route::put('/karyawan/{employee}', [AdminEmployeeController::class, 'update'])->name('admin.karyawan.update');
    Route::post('/karyawan/{employee}/access-rights', [AdminEmployeeController::class, 'updateAccessRights'])->name('admin.karyawan.access-rights');
    Route::post('/karyawan/{employee}/toggle-status', [AdminEmployeeController::class, 'toggleStatus'])->name('admin.karyawan.toggle-status');
    Route::delete('/karyawan/{employee}', [AdminEmployeeController::class, 'destroy'])->name('admin.karyawan.destroy');
    Route::get('/karyawan-verifikasi', [AdminEmployeeController::class, 'verifikasiIndex'])->name('admin.karyawan.verifikasi');
    Route::get('/karyawan-verifikasi/{registration}', [AdminEmployeeController::class, 'showRegistration'])->name('admin.karyawan.verifikasi.show');
    Route::post('/karyawan-verifikasi/{registration}/approve', [AdminEmployeeController::class, 'approveRegistration'])->name('admin.karyawan.verifikasi.approve');
    Route::post('/karyawan-verifikasi/{registration}/reject', [AdminEmployeeController::class, 'rejectRegistration'])->name('admin.karyawan.verifikasi.reject');
    Route::post('/karyawan-verifikasi/{registration}/reset', [AdminEmployeeController::class, 'resetRegistrationToPending'])->name('admin.karyawan.verifikasi.reset');
    Route::put('/karyawan-verifikasi/{registration}', [AdminEmployeeController::class, 'updateRegistrationNote'])->name('admin.karyawan.verifikasi.update');
    Route::delete('/karyawan-verifikasi/{registration}', [AdminEmployeeController::class, 'destroyRegistration'])->name('admin.karyawan.verifikasi.destroy');

    // Verifikasi & Persetujuan Reset Password / PIN Karyawan (Maker-Checker & SoD)
    Route::get('/karyawan-reset-password', [AdminEmployeeController::class, 'resetPasswordIndex'])->name('admin.karyawan.reset-password');
    Route::post('/karyawan-reset-password/{resetRequest}/approve', [AdminEmployeeController::class, 'approvePasswordReset'])->name('admin.karyawan.reset-password.approve');
    Route::post('/karyawan-reset-password/{resetRequest}/reject', [AdminEmployeeController::class, 'rejectPasswordReset'])->name('admin.karyawan.reset-password.reject');
    Route::put('/karyawan-reset-password/{resetRequest}', [AdminEmployeeController::class, 'updatePasswordReset'])->name('admin.karyawan.reset-password.update');
    Route::delete('/karyawan-reset-password/{resetRequest}', [AdminEmployeeController::class, 'destroyPasswordReset'])->name('admin.karyawan.reset-password.destroy');

    // Persetujuan Pembukaan Blokir (Unlock Approval) Akun Karyawan Akibat 3x Gagal Login
    Route::get('/karyawan-unlock', [AdminEmployeeController::class, 'unlockIndex'])->name('admin.karyawan.unlock');
    Route::get('/karyawan-unlock/{employee}', [AdminEmployeeController::class, 'showUnlockApproval'])->name('admin.karyawan.unlock.show');
    Route::post('/karyawan-unlock/{employee}', [AdminEmployeeController::class, 'processUnlockApproval'])->name('admin.karyawan.unlock.process');

    // Tinjauan Hak Akses Pengguna & De-provisioning (ISO/IEC 27001:2022 Klausul A.8.5)
    Route::get('/karyawan-access-review', [AdminEmployeeController::class, 'accessReviewIndex'])->name('admin.karyawan.access-review');
    Route::post('/karyawan-access-review/sign-off', [AdminEmployeeController::class, 'signOffAccessReview'])->name('admin.karyawan.access-review.sign-off');
    Route::get('/karyawan-access-review/export', [AdminEmployeeController::class, 'exportAccessReviewCsv'])->name('admin.karyawan.access-review.export');

    // Penjualan & POS Kasir ERP
    Route::get('/penjualan', [AdminSaleController::class, 'index'])->name('admin.penjualan');
    Route::post('/penjualan', [AdminSaleController::class, 'store'])->name('admin.penjualan.store');
    Route::post('/penjualan/permintaan-stok/{stockRequest}/status', [AdminSaleController::class, 'updateStockRequestStatus'])->name('admin.penjualan.stock-request.status');
    Route::post('/penjualan/rekap-shift/{summary}/verify-setor', [AdminSaleController::class, 'verifyShiftDeposit'])->name('admin.penjualan.verify-setor');
    Route::get('/penjualan/{sale}', [AdminSaleController::class, 'show'])->name('admin.penjualan.show');
    Route::put('/penjualan/{sale}', [AdminSaleController::class, 'update'])->name('admin.penjualan.update');
    Route::post('/penjualan/{sale}/status', [AdminSaleController::class, 'updateStatus'])->name('admin.penjualan.status');
    Route::delete('/penjualan/{sale}', [AdminSaleController::class, 'destroy'])->name('admin.penjualan.destroy');
    Route::get('/penjualan/{sale}/print', [AdminSaleController::class, 'printReceipt'])->name('admin.penjualan.print');

    // Presensi & Rekapitulasi ERP (Single Source of Truth & Audit Trail)
    Route::get('/presensi', [AdminAttendanceController::class, 'index'])->name('admin.presensi');
    Route::get('/presensi/export-audit', [AdminAttendanceController::class, 'exportDailyAuditCsv'])->name('admin.presensi.export-audit');
    Route::get('/presensi/{record}/detail', [AdminAttendanceController::class, 'showAuditDetail'])->name('admin.presensi.detail');
    Route::get('/presensi/rekap', [AdminAttendanceController::class, 'rekap'])->name('admin.presensi.rekap');
    Route::get('/presensi/approval', [AdminAttendanceController::class, 'approval'])->name('admin.presensi.approval');
    Route::get('/presensi/audit-logs', [AuditLogAdminController::class, 'index'])->name('admin.presensi.audit-logs');
    Route::post('/presensi/leave/{leave}/approve', [AdminAttendanceController::class, 'approveLeave'])->name('admin.presensi.leave.approve');
    Route::post('/presensi/leave/{leave}/reject', [AdminAttendanceController::class, 'rejectLeave'])->name('admin.presensi.leave.reject');
    Route::post('/presensi/overtime/{overtime}/approve', [AdminAttendanceController::class, 'approveOvertime'])->name('admin.presensi.overtime.approve');
    Route::post('/presensi/overtime/{overtime}/reject', [AdminAttendanceController::class, 'rejectOvertime'])->name('admin.presensi.overtime.reject');
    Route::post('/presensi/correction/{correction}/approve', [AdminAttendanceController::class, 'approveCorrection'])->name('admin.presensi.correction.approve');
    Route::post('/presensi/correction/{correction}/reject', [AdminAttendanceController::class, 'rejectCorrection'])->name('admin.presensi.correction.reject');
    Route::post('/presensi/swap/{swap}/approve', [AdminAttendanceController::class, 'approveShiftSwap'])->name('admin.presensi.swap.approve');
    Route::post('/presensi/swap/{swap}/reject', [AdminAttendanceController::class, 'rejectShiftSwap'])->name('admin.presensi.swap.reject');
    Route::post('/presensi/record-overtime/{record}/approve', [AdminAttendanceController::class, 'approveRecordOvertime'])->name('admin.presensi.record-overtime.approve');
    Route::post('/presensi/record-overtime/{record}/reject', [AdminAttendanceController::class, 'rejectRecordOvertime'])->name('admin.presensi.record-overtime.reject');
    Route::post('/presensi/record-early-leave/{record}/approve', [AdminAttendanceController::class, 'approveRecordEarlyLeave'])->name('admin.presensi.record-early-leave.approve');
    Route::get('/presensi/pengaturan', [AdminAttendanceSettingController::class, 'index'])->name('admin.presensi.pengaturan');
    Route::post('/presensi/pengaturan', [AdminAttendanceSettingController::class, 'update'])->name('admin.presensi.pengaturan.update');

    // Jam Kerja Karyawan CRUD (Edit & Delete Rekaman Presensi)
    Route::post('/jam-kerja', [AdminAttendanceController::class, 'storeWorkHours'])->name('admin.jamkerja.store');
    Route::put('/jam-kerja/{record}', [AdminAttendanceController::class, 'updateWorkHours'])->name('admin.jamkerja.update');
    Route::delete('/jam-kerja/{record}', [AdminAttendanceController::class, 'destroyWorkHours'])->name('admin.jamkerja.destroy');

    // Kehadiran Kegiatan (Meeting, Pelatihan, GC) CRUD
    Route::post('/kegiatan', [AdminAttendanceController::class, 'storeActivity'])->name('admin.kegiatan.store');
    Route::put('/kegiatan/{activity}', [AdminAttendanceController::class, 'updateActivity'])->name('admin.kegiatan.update');
    Route::delete('/kegiatan/{activity}', [AdminAttendanceController::class, 'destroyActivity'])->name('admin.kegiatan.destroy');
});

// Autentikasi Admin Maya Graha Kencana (Manager vs Staff)
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::match(['get', 'post'], '/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Dashboard & Profil Staff (1. Profil, 2. Username, 3. Ubah Password, 4. Jumlah Presensi, 5. Pendapatan Penjualan, 6. Gaji)
Route::get('/admin/staff/profile', [StaffDashboardController::class, 'profile'])->name('admin.staff.profile');
Route::post('/admin/staff/profile/password', [StaffDashboardController::class, 'updatePassword'])->name('admin.staff.password.update');
Route::get('/staff/profile', [StaffDashboardController::class, 'profile'])->name('staff.profile');
Route::get('/staff/dashboard', [StaffDashboardController::class, 'profile'])->name('staff.dashboard');
Route::get('/bagus/staff', [StaffDashboardController::class, 'profile']);
Route::get('/bagus/staff/profile', [StaffDashboardController::class, 'profile']);

Route::prefix('warnet')->group(function () {
    Route::post('pos/checkout', [InternetCafeController::class, 'checkout'])->name('warnet.checkout');
    Route::post('pc/toggle', [InternetCafeController::class, 'togglePcStation'])->name('warnet.pc-toggle');
    Route::post('attendance/clock', [InternetCafeController::class, 'clockAttendance'])->name('warnet.clock');
    Route::post('stock/transfer', [InternetCafeController::class, 'transferStock'])->name('warnet.stock-transfer');
    Route::post('schedule/store', [InternetCafeController::class, 'storeSchedule'])->name('warnet.schedule-store');
    Route::post('schedule/upload-static', [InternetCafeController::class, 'uploadStaticSchedule'])->name('warnet.schedule.upload-static');
    Route::put('schedule/{schedule}', [InternetCafeController::class, 'updateSchedule'])->name('warnet.schedule-update');
    Route::delete('schedule/{schedule}', [InternetCafeController::class, 'destroySchedule'])->name('warnet.schedule-destroy');
});

Route::prefix('bagus/warnet')->group(function () {
    Route::post('pos/checkout', [InternetCafeController::class, 'checkout']);
    Route::post('pc/toggle', [InternetCafeController::class, 'togglePcStation']);
    Route::post('attendance/clock', [InternetCafeController::class, 'clockAttendance']);
    Route::post('stock/transfer', [InternetCafeController::class, 'transferStock']);
    Route::post('schedule/store', [InternetCafeController::class, 'storeSchedule']);
    Route::post('schedule/upload-static', [InternetCafeController::class, 'uploadStaticSchedule']);
    Route::put('schedule/{schedule}', [InternetCafeController::class, 'updateSchedule']);
    Route::delete('schedule/{schedule}', [InternetCafeController::class, 'destroySchedule']);
});
