# Dokumen Perencanaan Hak Akses: Role-Based Access Control (RBAC) ERP

Dokumen ini berisi komponen wajib, prinsip keamanan, dan template matriks hak akses (*Access Matrix*) standar untuk implementasi sistem *Enterprise Resource Planning* (ERP) di perusahaan.

---

## 1. Komponen Wajib RBAC
Dalam menyusun RBAC yang aman dan akuntabel, 6 elemen berikut wajib didefinisikan secara tertulis sebelum dikonfigurasi ke dalam sistem:

1. **Daftar Pengguna Berdasarkan Akun Unik (Users)**
   * Setiap staf wajib memiliki satu akun login personal yang terikat dengan email kerja.
   * Dilarang keras menggunakan *shared accounts* (satu akun dipakai bersama) demi menjaga validitas *audit trail*.
2. **Matriks Peran Kerja (Roles)**
   * Pengelompokan hak akses berdasarkan fungsi jabatan, bukan individu (contoh: `Staf Gudang`, `Supervisor Akunting`).
3. **Izin Tindakan (Permissions / Access Rights)**
   * Hak spesifik yang diberikan pada setiap objek data dengan basis operasi **CRUD**:
     * **Create (C):** Membuat dokumen/data baru.
     * **Read (R):** Melihat atau membaca data.
     * **Update (U):** Mengubah data yang sudah ada.
     * **Delete (D):** Menghapus data atau membatalkan (*void*) dokumen.
4. **Batasan Ruang Lingkup Data (Data Scope / Record Rules)**
   * Aturan yang membatasi wilayah atau kepemilikan data yang boleh diakses oleh *role* yang sama (contoh: Sales Jakarta hanya bisa melihat data pelanggan wilayah Jakarta).
5. **Pemisahan Tugas (Segregation of Duties - SoD)**
   * Kebijakan sistem untuk mencegah *fraud*. *Role* yang menginput data transaksi dilarang memiliki hak akses untuk menyetujui (*approve*) transaksi tersebut.
6. **Manajemen Siklus Hidup Akun (User Lifecycle Management)**
   * Prosedur formal pemberian akses saat karyawan baru masuk (*onboarding*), perpindahan akses saat mutasi (*job transfer*), dan pencabutan akses seketika saat karyawan keluar (*offboarding*).

---

## 2. Prinsip Utama Keamanan ERP
* **Principle of Least Privilege (PoLP):** Pengguna hanya diberikan hak akses minimum yang mutlak diperlukan untuk menyelesaikan deskripsi pekerjaannya.
* **Audit Trail Accountability:** Setiap aktivitas pembuatan, perubahan, dan penghapusan data wajib tercatat otomatis oleh sistem beserta stempel waktu (*timestamp*) dan ID pengguna.

---

## 3. Template Matriks Hak Akses Standar (Access Matrix)

Berikut adalah contoh pemetaan hak akses operasional antar departemen:

| Modul / Fitur ERP | Peran: Staf Gudang | Peran: Supervisor Gudang | Peran: Staf Akuntansi | Peran: Manajer Keuangan |
| :--- | :---: | :---: | :---: | :---: |
| **Data Master Barang** | Read | Create, Read, Update | Read | Read |
| **Stok Opname** | Create, Read | Create, Read, Update, Delete | Read | Read |
| **Faktur Pembelian (Bill)** | No Access | Read | Create, Read, Update | Read, Approve |
| **Jurnal Keuangan** | No Access | No Access | Create, Read, Update | Create, Read, Update, Delete |
| **Laporan Laba Rugi** | No Access | No Access | Read | Create, Read, Update |
| **Konfigurasi Sistem** | No Access | No Access | No Access | No Access |

*Catatan: Nilai hak akses di atas dapat disesuaikan kembali dengan standar operational procedure (SOP) internal masing-masing perusahaan.*