# Panduan Koneksi Database Laravel Bagus ke Navicat

Dokumentasi ini merangkum pembahasan mengenai cara menghubungkan database aplikasi **Bagus** ke software GUI **Navicat**, penanganan skema SQL, serta solusi perbaikan error import file konfigurasi `.ncx`.

---

## 1. Ringkasan Database Aplikasi

- **DBMS Default**: PostgreSQL (v14/15/16+)
- **Host**: `192.168.5.10`
- **Port**: `5432`
- **Database**: `bagus`
- **Username**: `bagus`
- **Password**: `123456`
- **Default Schema**: `public`

> [!NOTE]
> Seluruh skema tabel dari file `database/schema/presensi_database_bagus.sql` dan `database/schema/shift_schedule_images.sql` **sudah aktif dan terpasang** di database PostgreSQL tersebut (total 40 tabel operasional).

---

## 2. Cara Menghubungkan ke Navicat

### Cara A: Input Manual (Direkomendasikan & Tercepat)

1. Buka Navicat.
2. Klik tombol **Connection** (di pojok kiri atas) → pilih **PostgreSQL**.
3. Isi parameter koneksi:
   - **Connection Name**: `bagus_pgsql` (bebas)
   - **Host**: `192.168.5.10`
   - **Port**: `5432`
   - **Initial Database**: `bagus`
   - **User Name**: `bagus`
   - **Password**: `123456`
4. Klik **Test Connection** hingga muncul pesan *Connection Successful*.
5. Klik **OK** untuk menyimpan.
6. Klik ganda pada koneksi tersebut di panel kiri, lalu navigasikan ke:
   `bagus` → `Schemas` → `public` → `Tables`.

---

### Cara B: Import Profil Koneksi `.ncx`

Telah disediakan file konfigurasi di:
`database/schema/bagus_connection.ncx`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Navicat>
    <Connection Name="bagus_pgsql" ServerType="PostgreSQL">
        <Host>192.168.5.10</Host>
        <Port>5432</Port>
        <UserName>bagus</UserName>
        <InitialCatalog>bagus</InitialCatalog>
    </Connection>
</Navicat>
```

**Langkah import:**
1. Di Navicat, pilih menu **File** → **Import Connections...**
2. Pilih file `bagus_connection.ncx`.
3. Klik **OK** / **Import**.
4. Buka koneksi `bagus_pgsql` dan masukkan password `123456`.

---

## 3. Catatan Penyelesaian Error "Error occurred while reading the file"

- **Penyebab**: Format XML file `.ncx` sebelumnya menggunakan tag non-standar (`<Connections>`, `<Connection ConnectionName=... ConnType=...>`) sehingga gagal dibaca oleh XML parser bawaan Navicat.
- **Solusi**: Struktur XML telah diperbarui menjadi format resmi Navicat (`<Navicat><Connection Name="..." ServerType="PostgreSQL">...`).

---

### Cara C: Koneksi Menggunakan SSH Tunnel (Rekomendasi Keamanan & Remote)

Metode SSH Tunnel sangat dianjurkan jika port `5432` PostgreSQL tidak dibuka langsung ke publik atau jika Anda mengakses database dari luar jaringan LAN kantor:

1. Di Navicat, buat koneksi baru: **Connection** → **PostgreSQL**.
2. **Tab General (Koneksi Database Lokal terhadap Server):**
   - **Connection Name**: `bagus_pgsql_ssh`
   - **Host**: `127.0.0.1` atau `localhost` *(karena port 5432 diakses dari sisi dalam server via tunnel)*
   - **Port**: `5432`
   - **Initial Database**: `bagus`
   - **User Name**: `bagus`
   - **Password**: `123456`
3. **Tab SSH (Terowongan Enkripsi ke Server Ubuntu):**
   - Centang opsi **Use SSH Tunnel**.
   - **Host**: `192.168.5.10` *(atau IP public server jika dari internet)*
   - **Port**: `22`
   - **User Name**: `bagus`
   - **Authentication Method**: `Password` atau `Public Key`
   - **Password**: Masukkan password user SSH server Anda.
4. Klik **Test Connection**. Navicat akan menguji jalur SSH Tunnel terlebih dahulu, lalu mengautentikasi PostgreSQL.
5. Klik **OK** untuk menyimpan.

> [!TIP]
> File profil import otomatis untuk SSH Tunnel juga telah disediakan di:
> [`database/schema/bagus_connection_ssh.ncx`](file:///var/www/laravel_apps/bagus/database/schema/bagus_connection_ssh.ncx). Anda cukup mengimpor file tersebut via **File** → **Import Connections...** lalu mengisi password database dan password SSH.

---

## 4. Konfigurasi Sisi Server (PostgreSQL & Jaringan)

Jika Anda menggunakan koneksi direct (Cara A/B) dan menghadapi kendala jaringan, pastikan dua konfigurasi utama PostgreSQL di server telah aktif:

### 4.1. Membuka Listen Addresses (`postgresql.conf`)
Pastikan PostgreSQL menerima koneksi dari alamat IP jaringan:
```conf
# Lokasi: /etc/postgresql/14/main/postgresql.conf
listen_addresses = '*'
port = 5432
```
> [!NOTE]
> Server PostgreSQL saat ini telah aktif mendengarkan koneksi pada seluruh interface (`0.0.0.0:5432` dan `[::]:5432`).

### 4.2. Memberi Izin Host Client (`pg_hba.conf`)
Agar PostgreSQL mengizinkan client dari segmen IP LAN (`192.168.5.x`) untuk masuk ke database `bagus`:
```conf
# Lokasi: /etc/postgresql/14/main/pg_hba.conf
# TYPE  DATABASE        USER            ADDRESS                 METHOD
host    bagus           bagus           192.168.5.0/24          scram-sha-256
host    all             all             127.0.0.1/32            scram-sha-256
```
Setelah mengubah berkas di atas, terapkan konfigurasi tanpa mematikan database:
```bash
sudo systemctl reload postgresql
```

### 4.3. Konfigurasi Firewall (UFW)
Jika firewall Ubuntu aktif di server, izinkan port 5432:
```bash
sudo ufw allow 5432/tcp
sudo ufw status
```

---

## 5. Konfigurasi Lanjutan Navicat (Advanced & Connection Tuning)

Untuk stabilitas performa saat mengelola database:

### 5.1. Tab Advanced
1. Klik kanan pada nama koneksi `bagus_pgsql` → pilih **Connection Properties...**
2. Buka tab **Advanced**:
   - **Encoding**: Pilih `65001 (UTF-8)` untuk memastikan karakter bahasa dan simbol tersimpan sempurna.
   - **Keepalive Interval (seconds)**: Isi dengan `60` detik. Fitur ini mengirimkan sinyal ping berkala agar koneksi Navicat ke PostgreSQL tidak terputus (*timeout*) saat komputer dibiarkan idle.
   - **Auto Connect**: Centang opsi ini jika ingin koneksi otomatis terbuka ketika Navicat pertama kali dijalankan.

### 5.2. Tab SSL
- **Opsi SSL**: Biarkan **tidak dicentang (Disabled)** untuk koneksi direct LAN atau jika sudah menggunakan SSH Tunnel, guna menghindari overhead negosiasi sertifikat self-signed.

---

## 6. Panduan Eksplorasi & Operasional Database di Navicat

Setelah berhasil terhubung, berikut fitur-fitur utama Navicat untuk menunjang operasional aplikasi Bagus:

### 6.1. Menavigasi Objek Database
1. Buka koneksi `bagus_pgsql` → buka node database `bagus`.
2. Masuk ke **Schemas** → **public** → **Tables**.
3. Seluruh **40 tabel operasional** akan tampil, mencakup:
   - Modul Presensi & Kepegawaian: `branches`, `employees`, `shifts`, `attendance_records`, `leave_requests`, `overtime_claims`, `attendance_corrections`, `shift_swap_requests`, `shift_schedule_images`.
   - Modul POS Warnet & Retail: `pc_stations`, `cafe_sales`, `cafe_sale_items`, `products`, `product_variants`, `stock_inventories`, dll.
   - Modul Sistem: `users`, `sessions`, `jobs`, `cache`, `migrations`.

### 6.2. Visualisasi Skema ERD (Reverse Engineering)
Untuk melihat hubungan relasi foreign key dan desain tabel secara visual:
1. Klik menu bar atas **Tools** (atau **Model**) → pilih **Reverse Database to Model...**
2. Pilih koneksi `bagus_pgsql` → database `bagus` → schema `public`.
3. Klik **Start**. Navicat akan menghasilkan diagram relasi tabel (ERD) lengkap yang rapi dan interaktif.

### 6.3. Menjalankan Skrip & Query Kustom
1. Klik tombol **Query** di toolbar atas → **New Query**.
2. Anda dapat mengecek ringkasan presensi secara real-time dengan contoh query:
   ```sql
   SELECT 
       e.name AS nama_karyawan,
       b.name AS nama_cabang,
       s.name AS nama_shift,
       a.clock_in_at,
       a.clock_out_at,
       a.status
   FROM attendance_records a
   JOIN employees e ON e.id = a.employee_id
   JOIN branches b ON b.id = a.branch_id
   JOIN shifts s ON s.id = a.shift_id
   ORDER BY a.clock_in_at DESC
   LIMIT 10;
   ```
3. Klik tombol **Run** (ikon segitiga hijau).

### 6.4. Backup (Dump SQL) & Restore Database
- **Membuat File Backup:**
  1. Klik kanan pada database `bagus` atau schema `public`.
  2. Pilih **Dump SQL File** → **Structure and Data...**
  3. Tentukan folder penyimpanan lokal dan klik **Start**.
- **Menjalankan / Restore File SQL:**
  1. Klik kanan pada database `bagus` atau schema `public`.
  2. Pilih **Execute SQL File...**
  3. Cari file skema yang ingin dijalankan (misal: [`database/schema/presensi_database_bagus.sql`](file:///var/www/laravel_apps/bagus/database/schema/presensi_database_bagus.sql)).
  4. Klik **Start** hingga proses selesai dengan *Successfully*.

---

## 7. Matriks Troubleshooting Masalah Koneksi Navicat

| Gejala / Pesan Error | Kemungkinan Penyebab | Langkah Solusi |
|---|---|---|
| `FATAL: password authentication failed for user "bagus"` | Password salah atau tipe enkripsi password tidak cocok | Periksa kembali password di Navicat (`123456`). Pastikan username `bagus` huruf kecil semua. |
| `FATAL: no pg_hba.conf entry for host "192.168.5.x"` | PostgreSQL menolak koneksi dari IP client tersebut | Tambahkan baris `host bagus bagus 192.168.5.0/24 scram-sha-256` pada `/etc/postgresql/14/main/pg_hba.conf` lalu jalankan `sudo systemctl reload postgresql`. Atau gunakan **Cara C (SSH Tunnel)**. |
| `could not connect to server: Connection refused` | Layanan PostgreSQL mati atau port 5432 tidak listen | Pastikan service aktif via `sudo systemctl status postgresql` dan pastikan `listen_addresses = '*'` di `postgresql.conf`. |
| `Connection timed out` | Terhalang firewall server atau router jaringan | Izinkan port pada firewall: `sudo ufw allow 5432/tcp`. Jika lintas internet/VPN, gunakan metode **SSH Tunnel**. |
| `SSH Tunnel: Authentication failed` | Kredensial SSH salah | Pastikan username, port (22), dan password SSH server `192.168.5.10` sudah tepat. |
| `Error occurred while reading the file (.ncx)` | Tag XML file import tidak dikenali Navicat | Gunakan file konfigurasi resmi yang telah diperbarui di [`database/schema/bagus_connection.ncx`](file:///var/www/laravel_apps/bagus/database/schema/bagus_connection.ncx) atau gunakan **Cara A (Manual)**. |

