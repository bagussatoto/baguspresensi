# Dokumen Matriks Hak Akses: Role-Based Access Control (RBAC) ERP & Presensi
**PT Maya Graha Kencana (MGK)**  
*Standar Kepatuhan: ISO/IEC 27001:2022, Segregation of Duties (SoD), & Single Source of Truth (SoT)*

---

## 1. Komponen Wajib RBAC Sistem MGK

Sesuai dengan arsitektur sistem Presensi, POS Kasir, dan ERP terpadu MGK, 6 elemen hak akses berikut diterapkan:

1. **Daftar Pengguna Berdasarkan Akun Unik (Users)**
   * Setiap staf wajib memiliki akun login mandiri (`username` / `NIK` unik dan password/PIN terenkripsi).
   * Dilarang keras menggunakan *shared accounts* (satu akun dipakai bersama) untuk menjamin validitas rekam jejak audit (*Audit Trail WORM*).
2. **Matriks Peran Kerja (Roles)**
   * Hak akses dikelompokkan berdasarkan peran fungsional sistem (`App\Domains\Attendance\Enums\AdminRole`):
     - **Kasir / Operator Shift:** Bertanggung jawab atas presensi, operasional transaksi POS, cek stok fisik harian, dan penginputan serah terima kasir.
     - **Supervisor / Branch Admin:** Memantau kehadiran staf cabang, mengawal operasional toko, dan mengeksekusi penyetoran kas ke bank/kolektor.
     - **Super Admin / Owner (Pusat):** Memiliki wewenang penuh atas otorisasi tingkat tinggi (approval klaim billing PLN, buka kunci shift, resolusi selisih kas, dan master data).
     - **Auditor Kepatuhan (Read-Only):** Memiliki akses baca penuh untuk keperluan audit keuangan, perpajakan, dan verifikasi kepatuhan ISO tanpa izin mengubah data transaksi.
3. **Izin Tindakan (Permissions / Access Rights)**
   * Berbasis operasi kontrol:
     - **Create (C):** Menginput transaksi baru, check-in presensi, atau mengajukan koreksi.
     - **Read (R):** Melihat atau mencetak slip/laporan.
     - **Update (U):** Mengubah data yang belum terkunci.
     - **Delete / Void (D):** Membatalkan transaksi dengan otorisasi supervisor.
     - **Approve / Otorisasi (A):** Hak khusus menyetujui klaim gangguan, membuka shift terkunci, atau mengesahkan penyelesaian selisih kas.
4. **Batasan Ruang Lingkup Data (Data Scope / Branch Scoping)**
   * Sistem membatasi akses berdasarkan kolom `assigned_branch_id`:
     - Staf & Supervisor hanya dapat melihat dan memproses data pada cabangnya masing-masing.
     - Super Admin dan Auditor memiliki akses lintas-cabang (*Multi-Branch Consolidation*).
5. **Pemisahan Tugas (Segregation of Duties - SoD)**
   * Mencegah kecurangan (*fraud*) internal:
     - Kasir yang mengajukan klaim penyesuaian billing PLN Rp 0 dilarang menyetujui klaimnya sendiri.
     - Kasir yang mengalami selisih kas laci minus tidak dapat memutuskan sendiri status penyelesaian selisihnya (wajib diputuskan oleh Super Admin/Owner).
6. **Manajemen Siklus Hidup Akun (User Lifecycle Management)**
   * Prosedur formal pendaftaran mandiri karyawan baru dengan pola *Maker-Checker* (HRD verifikasi), rotasi PIN/password, dan penonaktifan akun seketika saat karyawan nonaktif (*offboarding*).

---

## 2. Prinsip Utama Keamanan & Audit

* **Principle of Least Privilege (PoLP):** Pengguna hanya diberikan wewenang minimum yang mutlak diperlukan untuk menyelesaikan tugas shiftnya.
* **WORM Audit Trail (Write Once, Read Many):** Setiap aktivitas krusial (buka kunci shift, persetujuan diskon, selisih kas fisik, dan setoran bank) otomatis tercatat ke tabel `audit_logs` bersama stempel waktu, user ID, dan alamat IP.

---

## 3. Matriks Hak Akses Operasional Sistem MGK (Access Matrix)

Pemetaan wewenang operasional pada portal Presensi V2, Kasir POS Desktop, dan Admin ERP:

| Modul & Fitur Sistem | Kasir / Operator Shift | Supervisor Cabang (Branch Admin) | Super Admin / Owner (Pusat) | Auditor Kepatuhan (Internal/External) |
| :--- | :---: | :---: | :---: | :---: |
| **1. Presensi GPS & Selfie (`/presensi2/scan`)** | **Create, Read**<br>*(Shift & Akun Sendiri)* | **Read**<br>*(Monitoring Cabang)* | **CRUD & Otorisasi**<br>*(Koreksi, Cuti, Lembur)* | **Read-Only**<br>*(Arsip Log Absensi)* |
| **2. Audit Cek Stok Shift (`/presensi2/stok`)** | **Create, Read**<br>*(Blind Count Fisik Shift)* | **Read, Verifikasi**<br>*(Cek Selisih Toko)* | **Read, Adjust, Ekspor**<br>*(Rekonsiliasi Persediaan)* | **Read-Only**<br>*(Kertas Kerja Audit)* |
| **3. Transaksi POS Kasir (`/pos`)** | **Create, Read**<br>*(Input Nota, Tunai, QRIS)* | **Read, Void / Batal**<br>*(Otorisasi Pembatalan)* | **Full Access**<br>*(Setting Harga & Diskon)* | **Read-Only**<br>*(Laporan Transaksi)* |
| **4. Serah Terima Shift (`/serah-terima-v2`)** | **Create, Read**<br>*(Hitung Fisik Kas Laci)* | **Read, Setor**<br>*(Kirim ke Bank / Kolektor)* | **Full Access**<br>*(Verifikasi Terima Dana)* | **Read-Only**<br>*(Arsip Bukti Kwitansi)* |
| **5. Buka Kunci Shift (`unlock-sales`)** | ❌ **No Access** | ❌ **No Access** | **Approve / Buka Kunci**<br>*(Re-Open Shift Terkunci)* | **Read-Only**<br>*(Log Rekam Jejak)* |
| **6. Klaim Penyesuaian Billing PLN & Print** | **Create**<br>*(Ajukan Klaim Gangguan)* | ❌ **No Access** | **Approve / Reject**<br>*(Potong Target Setor Kasir)* | **Read-Only**<br>*(Verifikasi Bukti)* |
| **7. Tindak Lanjut Selisih Kas Laci Minus** | ❌ **No Access** | ❌ **No Access** | **Resolve / Eksekusi**<br>*(Ganti Tunai, Kasbon, Beban)*| **Read-Only**<br>*(Berita Acara Selisih)* |
| **8. Permintaan Transfer Stok Cabang (STO)** | **Create**<br>*(Draf Permintaan Barang)* | **Read, Review**<br>*(Persetujuan Cabang)* | **Approve & Dispatch**<br>*(Pengiriman dari DC Pusat)* | **Read-Only**<br>*(Kartu Mutasi Barang)* |
| **9. Jurnal Akuntansi SAK & Buku Besar** | ❌ **No Access** | **Read**<br>*(Jurnal Kas Toko)* | **CRUD Penuh**<br>*(Neraca, Laba Rugi, Penutupan)*| **Read-Only**<br>*(Kertas Kerja SAK)* |
| **10. Manajemen Akun & Pengaturan Sistem** | ❌ **No Access** | ❌ **No Access** | **Full Configuration**<br>*(User, Shift, Master Data)* | **Read-Only**<br>*(Konfigurasi Keamanan)* |

---

## 4. SOP Tindak Lanjut Pelanggaran & Pengecualian Akses

1. **Penguncian Otomatis Gagal Login:** Percobaan gagal login 3 kali berturut-turut memicu penguncian sementara akun (*progressive lockout*) dan peringatan dini WhatsApp ke HRD/Owner.
2. **Pengecualian Darurat (*Emergency Access / Break-Glass*):** Jika Super Admin berhalangan saat terjadi kendala operasional darurat di cabang, otorisasi hanya dapat didelegasikan sementara melalui pencatatan tertulis di log sistem dengan verifikasi dua arah (Owner approval via OTP/WhatsApp resmi).
3. **Tinjauan Berkala (*Periodic Access Review*):** Hak akses seluruh peran dievaluasi minimal setiap 3 bulan sekali untuk memastikan kesesuaian dengan mutasi kerja dan tanggung jawab terkini.
