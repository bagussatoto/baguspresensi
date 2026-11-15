# Spesifikasi Modul: Monitoring Jadwal Cron, Queue Worker & Backup Database Otomatis

**Tanggal:** 15 November 2026  
**Proyek:** ERP PT Maya Graha Kencana (MGK)  
**Target Lokasi Menu:** Pusat Kendali ERP (`/admin/ERP` -> `/admin/ERP/system-monitor`)  
**Status:** Rancangan Modul (Blueprint)

---

## 1. Latar Belakang & Tujuan

Sistem ERP MGK menjalankan transaksi POS, jurnal akuntansi *double-entry*, mutasi stok, serta absensi karyawan secara harian. Saat ini [routes/console.php](file:///var/www/laravel_apps/bagus/routes/console.php) telah memiliki jadwal otomatis harian (`attendance:close-daily` pukul `23:59` dan `registrations:prune-expired` pukul `01:00`), serta konfigurasi antrean `QUEUE_CONNECTION=database`.

Namun, belum terdapat:
1. Skrip backup database PostgreSQL otomatis yang terjadwal sebelum dan sesudah jam operasional cabang.
2. Pencatatan riwayat backup beserta verifikasi integritas file (*checksum*) dan retensi otomatis agar disk server tidak penuh.
3. Halaman pemantauan (*monitoring*) di panel Admin agar manajemen/IT dapat melihat kesehatan **Cron Scheduler**, **Queue Worker**, dan **Backup Database** tanpa harus melakukan SSH ke server.

---

## 2. Ruang Lingkup Fitur

1. **Backup Database Otomatis (PostgreSQL)**:
   - Eksekusi `pg_dump` yang dikompresi (`.sql.gz`) dan disimpan ke `storage/app/backups/database/`.
   - Jadwal otomatis dua kali sehari:
     - **06:00 WIB** (sebelum operasional cabang dibuka).
     - **23:30 WIB** (setelah tutup kasir/shift cabang).
   - Retensi otomatis (default **14 hari**): file backup lama dihapus otomatis setelah backup baru berhasil.
2. **Monitoring Jadwal Cron (Scheduler Heartbeat)**:
   - Mencatat waktu eksekusi terakhir, durasi, dan status (`success`/`failed`) dari setiap perintah terjadwal di Laravel Scheduler.
   - Memberi indikator peringatan (*warning/stale*) jika scheduler tidak berdetak melewati batas wajar.
3. **Monitoring Queue Worker (`jobs` & `failed_jobs`)**:
   - Menampilkan jumlah antrean aktif (`jobs`) dan pekerjaan gagal (`failed_jobs`).
   - Menyediakan aksi cepat **Retry Job** dan **Flush Failed Jobs** langsung dari UI Admin.
4. **Aksi Manual & Audit Trail**:
   - Tombol **Backup Sekarang** (dijalankan via Queue atau sinkron).
   - Tombol **Unduh File Backup** (terproteksi RBAC & dicatat ke WORM Audit Log).

---

## 3. Arsitektur Database

### 3.1. Tabel `database_backups`
Menyimpan riwayat setiap proses backup database.

| Kolom | Tipe | Keterangan |
| :--- | :--- | :--- |
| `id` | `bigIncrements` | Primary key |
| `file_name` | `string` | Nama file (contoh: `mgk_erp_2026-11-15_233000.sql.gz`) |
| `file_path` | `string` | Path relatif pada disk `local` (`backups/database/...`) |
| `disk` | `string` | Nama filesystem disk (`local`) |
| `driver` | `string` | Driver DB (`pgsql`, `sqlite`, dll.) |
| `database_name` | `string` | Nama database target |
| `file_size_bytes` | `unsignedBigInteger` | Ukuran file dalam byte |
| `checksum_sha256` | `string(64)` nullable | Hash SHA-256 untuk verifikasi integritas file |
| `trigger_type` | `string(20)` | `scheduled`, `manual`, atau `cli` |
| `status` | `string(20)` | `pending`, `running`, `success`, `failed` |
| `triggered_by` | `unsignedBigInteger` nullable | ID admin pemicu (jika manual) |
| `duration_ms` | `unsignedInteger` nullable | Lama proses backup (milidetik) |
| `error_message` | `text` nullable | Pesan error jika status `failed` |
| `started_at` | `timestamp` nullable | Waktu mulai |
| `finished_at` | `timestamp` nullable | Waktu selesai |
| `created_at` / `updated_at` | `timestamps` | Standar Laravel |

### 3.2. Tabel `cron_heartbeats`
Menyimpan status eksekusi terakhir setiap jadwal cron/command.

| Kolom | Tipe | Keterangan |
| :--- | :--- | :--- |
| `id` | `bigIncrements` | Primary key |
| `command_name` | `string` unique | Nama command (contoh: `db:backup`, `attendance:close-daily`) |
| `schedule_expression` | `string` | Ekspresi jadwal (contoh: `0 6,23:30 * * *`) |
| `status` | `string(20)` | `running`, `success`, `failed` |
| `duration_ms` | `unsignedInteger` nullable | Durasi eksekusi terakhir |
| `output_summary` | `text` nullable | Ringkasan output command |
| `last_started_at` | `timestamp` nullable | Waktu mulai terakhir |
| `last_finished_at` | `timestamp` nullable | Waktu selesai terakhir |
| `created_at` / `updated_at` | `timestamps` | Standar Laravel |

---

## 4. Rencana File & Komponen Kode

### 4.1. Models
- `app/Models/DatabaseBackup.php`
  - Casts: `started_at`, `finished_at` -> `datetime`; `file_size_bytes`, `duration_ms` -> `integer`.
  - Accessor/Helper: `getFormattedSizeAttribute()` (KB/MB/GB), `fileExists()`.
- `app/Models/CronHeartbeat.php`
  - Casts: `last_started_at`, `last_finished_at` -> `datetime`.

### 4.2. Services
- `app/Services/DatabaseBackupService.php`
  - `runBackup(string $triggerType = 'cli', ?int $triggeredBy = null): DatabaseBackup`
    - Menggunakan `Symfony\Component\Process\Process` untuk menjalankan `pg_dump` dengan `PGPASSWORD` via environment variable proses (tidak terekspos di argumen CLI) dan kompresi `gzip`.
    - Pada environment pengujian (`sqlite`), menyediakan jalur aman (copy file sqlite atau simulasi dump terkontrol) agar feature test berjalan deterministik.
    - Menghitung `hash_file('sha256', ...)` dan mencatat ukuran serta durasi.
  - `pruneExpiredBackups(int $retentionDays = 14): int`
    - Menghapus file fisik dan menandai/menghapus record yang lebih tua dari `$retentionDays`.
- `app/Services/SystemMonitoringService.php`
  - Mengambil ringkasan status:
    - Status `cron_heartbeats` (sehat vs terlambat/gagal).
    - Jumlah `jobs` (antrean aktif) dan daftar `failed_jobs` (terbaru 20 baris).
    - Statistik backup (backup sukses terakhir, total file, total kapasitas disk terpakai, ruang disk tersedia `disk_free_space`).

### 4.3. Artisan Commands & Queue Jobs
- `app/Console/Commands/BackupDatabaseCommand.php`
  - Signature: `db:backup {--trigger=cli} {--retention=14}`
  - Menjalankan `DatabaseBackupService::runBackup()`, lalu `pruneExpiredBackups()`, serta memperbarui `cron_heartbeats`.
- `app/Jobs/RunDatabaseBackupJob.php`
  - Implements `ShouldQueue`. Dipanggil saat admin menekan tombol **Backup Sekarang** dari UI agar tidak memblokir request HTTP.
- Pembaruan `routes/console.php`:
  ```php
  Schedule::command('db:backup --trigger=scheduled')->dailyAt('06:00');
  Schedule::command('db:backup --trigger=scheduled')->dailyAt('23:30');
  ```

### 4.4. Controller, Routes & Keamanan
- Controller: `app/Http/Controllers/Admin/AdminSystemMonitorController.php`
  - `index(Request $request)`: Tampilkan dashboard monitoring (mendukung `?format=json`).
  - `triggerBackup(Request $request)`: Dispatch `RunDatabaseBackupJob` (atau opsi sinkron) + catat WORM Audit Log.
  - `downloadBackup(DatabaseBackup $backup)`: Stream file `.sql.gz` jika file ada + catat WORM Audit Log.
  - `retryFailedJob(string $uuid)`: Jalankan `Artisan::call('queue:retry', ['id' => [$uuid]])` + Audit Log.
  - `flushFailedJobs()`: Jalankan `Artisan::call('queue:flush')` + Audit Log.
- Routes (`routes/web.php` di dalam grup admin + alias `/bagus/admin/...`):
  - `GET /admin/ERP/system-monitor` -> `admin.erp.system-monitor`
  - `POST /admin/ERP/system-monitor/backup` -> `admin.erp.system-monitor.backup`
  - `GET /admin/ERP/system-monitor/backups/{backup}/download` -> `admin.erp.system-monitor.download`
  - `POST /admin/ERP/system-monitor/queue/retry/{uuid}` -> `admin.erp.system-monitor.queue-retry`
  - `POST /admin/ERP/system-monitor/queue/flush` -> `admin.erp.system-monitor.queue-flush`
- **Proteksi RBAC & SoD**:
  - Role `Auditor` hanya diizinkan melihat (`Read-Only`) — tidak dapat memicu backup, mengunduh dump mentah, atau menghapus `failed_jobs`.
  - Aksi mutasi/unduh wajib melalui middleware otorisasi Admin & tercatat di `AdminAuditLog` / WORM log.

### 4.5. Antarmuka UI
- View baru: `resources/views/admin/erp/system-monitor.blade.php`
  - **Baris 1 (4 Kartu KPI)**: Status Scheduler, Antrean & Failed Jobs, Backup Sukses Terakhir, Kapasitas Disk Backup.
  - **Baris 2 (Jadwal Cron Aktif)**: Tabel command terjadwal beserta status heartbeat terakhir & durasi.
  - **Baris 3 (Riwayat Backup Database)**: Tabel file backup, tipe pemicu, ukuran, SHA-256, status, dan tombol **Unduh**. Dilengkapi tombol **Backup Sekarang**.
  - **Baris 4 (Queue Failed Jobs)**: Tabel job gagal dengan tombol **Retry** dan **Flush All**.
- Tautan modul baru pada halaman **Pusat Kendali ERP** (`AdminErpHubController` / `erp.blade.php`).

---

## 5. Rencana Pengujian (`tests/Feature/AdminSystemMonitorTest.php`)

1. `test_admin_can_view_system_monitor_dashboard`: Memastikan halaman `/admin/ERP/system-monitor` merender KPI, status cron, dan riwayat backup.
2. `test_admin_can_trigger_manual_database_backup`: Memastikan pemicu backup manual membuat record `DatabaseBackup` berstatus `success` dan mencatat audit log.
3. `test_admin_can_download_existing_backup_file`: Memastikan file backup dapat diunduh dengan header attachment yang valid.
4. `test_backup_retention_prunes_expired_files`: Memastikan file & record backup yang melewati batas hari retensi terhapus otomatis oleh command `db:backup`.
5. `test_auditor_role_cannot_trigger_backup_or_flush_queue`: Memastikan prinsip *Read-Only* untuk role Auditor tetap terjaga.

---

## 6. Prasyarat Infrastruktur Server (Production)

Agar jadwal otomatis berjalan penuh di server Linux production, pastikan dua service berikut aktif di server:
1. **Cron Entry** (untuk Laravel Scheduler):
   ```cron
   * * * * * cd /var/www/laravel_apps/bagus && php artisan schedule:run >> /dev/null 2>&1
   ```
2. **Supervisor / Systemd** (untuk Queue Worker):
   ```ini
   php artisan queue:work database --sleep=3 --tries=3 --timeout=300
   ```
