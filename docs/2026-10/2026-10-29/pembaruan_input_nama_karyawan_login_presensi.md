# 📝 Dokumentasi Pembaruan Login Presensi v2: Input Teks Nama Karyawan

**Sistem Presensi Maya Graha Kencana (MGK)**  
*Dokumen Catatan Evaluasi & Implementasi Teknis*  
**Tanggal:** 29 Oktober 2026  
**URL Portal:** `http://192.168.5.10:8086/bagus/presensi2/login`

---

## 📌 Latar Belakang & Kebutuhan

Sebelumnya pada halaman login Presensi v2 (`/presensi2/login`) dan portal login layanan khusus (`/presensi2/layanan/login`), identitas karyawan dipilih melalui elemen `<select>` dropdown daftar nama lengkap.

Hal ini menimbulkan kebutuhan perbaikan:
1. **Privasi & Keamanan (Pencegahan User Enumeration):** Daftar seluruh staf karyawan aktif tidak boleh terpapar secara terbuka di layar publik/perangkat bersama.
2. **Kenyamanan Input:** Karyawan menginginkan kolom isian teks manual (`<input type="text">`) agar bisa langsung menuliskan nama karyawan tanpa harus menggulir daftar panjang atau memilih dari list dropdown.
3. **Pemberitahuan Peringatan Dini (Early Warning):** Upaya pelanggaran atau penyalahgunaan identitas akun tetap diawasi secara otomatis oleh sistem notifikasi keamanan WhatsApp/Telegram.

---

## 🛠️ Rincian Perubahan yang Diterapkan

### 1. Halaman Login Presensi v2 (`resources/views/presensi2/login.blade.php`)
* **Menghapus Elemen `<select>` & `<datalist>`:** Seluruh opsi pilihan nama karyawan ditiadakan 100%.
* **Mengubah Menjadi Input Teks:**
  ```html
  <!-- Tulis Identitas Karyawan (Murni Input Teks) -->
  <div class="form-group-custom" id="group-list-input">
      <label for="employee_name" class="form-label-custom">
          <span>Identitas Karyawan</span>
          <span style="font-size: 11px; color: #06b6d4; font-weight: normal;">(Tulis Nama Karyawan)</span>
      </label>
      <div class="input-field-wrap">
          <span class="input-leading-icon">👤</span>
          <input 
              type="text"
              name="employee_name" 
              id="employee_name" 
              class="form-control-custom"
              placeholder="Ketik nama karyawan Anda..."
              value="{{ old('employee_name') }}"
              required
              autofocus
              autocomplete="off"
          >
      </div>
  </div>
  ```
* **Pembaruan Petunjuk Akses:** Dialog bantuan dan label form telah disesuaikan menjadi panduan *"Tulis Nama Karyawan Anda"*.

---

### 2. Halaman Login Portal Layanan (`resources/views/presensi2/layanan-login.blade.php`)
* Diterapkan perubahan yang serupa untuk menu login layanan khusus:
  - General Cleaning (GC)
  - Meeting & Briefing
  - Back Office (BO) / Training
  - Ambil Stok
* Seluruh dropdown identitas karyawan digantikan dengan kolom input teks murni.

---

### 3. Logika Backend Controller (`AttendanceWebV2Controller.php`)
Fungsi `login()` dan `layananLogin()` diperbarui untuk menangani input string nama karyawan dengan fleksibilitas pencocokan:
1. **Pencocokan Nama Langsung:** Pencarian *case-insensitive* `LOWER(name) = ?`.
2. **Pencocokan Multi-Identifier:** Tetap mendukung jika staf mengetikkan NIK/Kode Karyawan (`code`), Username, atau Email.
3. **Pencocokan Nama Sebagian (*Partial Match*):** Jika nama yang dimasukkan unik (1 hasil), sistem langsung mengizinkan autentikasi kredensial. Jika terdapat nama kembar, sistem meminta penulisan nama lengkap spesifik.

---

### 4. Standarisasi Halaman Login ERP & Kios Warnet
* **Login Utama ERP (`resources/views/auth/login.blade.php`):**
  - Dropdown daftar Manager (`managerIdentifier`) dan daftar Staff (`staffIdentifier`) telah diganti dengan **kolom isian teks manual (`<input type="text">`)**.
  - Menghapus opsi manual dropdown ganda dan menyatukan pengalaman pengguna agar konsisten menulis nama, NIK, atau email.
* **Kios Presensi Warnet (`resources/views/warnet/index.blade.php` & `InternetCafeController.php`):**
  - Mengganti `<select id="attEmployeeSelect">` menjadi `<input type="text" id="attEmployeeInput">` pada form presensi kasir warnet.
  - Menyelaraskan fungsi API `clockAttendance` agar mendukung pencarian identitas operator kasir berdasarkan ketikan nama atau NIK.

---

### 5. Hasil Pengujian & Verifikasi
* **Pembersihan Cache:** Menjalankan `php artisan view:clear` dan `php artisan cache:clear`.
* **Automated Feature Tests:**
  - `AttendanceWebV2PortalTest`: 40 tests passed, 209 assertions.
  - `AdminAuthMultiRoleTest`: 8 tests passed, 40 assertions.
  - `InternetCafeTest` (Admin Login flow): 4 tests passed, 17 assertions.
* **Standar Kode:** Lolos audit format Laravel Pint (`vendor/bin/pint`).
