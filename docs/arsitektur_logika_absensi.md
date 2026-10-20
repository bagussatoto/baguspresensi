# Dokumen Arsitektur Logika & Desain Data Aplikasi Absensi Hybrid

Dokumen ini berisi panduan implementasi teknis untuk sistem absensi yang mengakomodasi dua jenis pekerjaan berbeda dalam satu aplikasi: **Pekerjaan Pabrik (Berbasis Jadwal/Shift)** dan **Pekerjaan Design (Berbasis Durasi Kontrak/Fleksibel)** tanpa merusak integritas data akuntansi.

---

## 1. Peta Paradigma Sistem (Systems Thinking)

Untuk menghindari bias dan kebuntuan logika (*silo berpikir*), tim harus memisahkan absensi menjadi 3 faktor atomik:
1. **Atom Waktu Aktual (Timestamp):** Catatan mentah kapan karyawan menekan tombol masuk/pulang (Jujur dan tanpa logika bisnis).
2. **Atom Aturan Bisnis (Konteks Roster):** Batasan kerja yang ditugaskan pada hari tersebut (Pabrik dengan Shift kaku vs Design dengan durasi fleksibel).
3. **Atom Output Keuangan (Durasi Kerja Efektif):** Hasil akhir berupa `Jumlah_Menit` atau `Jumlah_Jam` kerja bersih yang akan dikonsumsi oleh modul Akuntansi/Payroll.

---

## 2. Struktur Database (Entity Relationship Data Design)

Jangan menyatukan parameter shift dan fleksibel dalam satu kolom kaku. Gunakan relasi tabel terpisah dengan menaruh **Roster Jadwal Harian** sebagai jembatan konteksnya.

### A. Tabel `karyawan` (Master Data)
```sql
CREATE TABLE karyawan (
    id_karyawan INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    kontrak_design_default ENUM('6_jam', '8_jam', 'none') DEFAULT 'none'
);
```

### B. Tabel `master_shift_pabrik` (Konfigurasi Shift)
```sql
CREATE TABLE master_shift_pabrik (
    id_shift INT PRIMARY KEY AUTO_INCREMENT,
    nama_shift VARCHAR(50), -- Shift 1, Shift 2, Shift 3
    jam_masuk TIME,         -- 08:00:00, 16:00:00, 00:00:00
    jam_pulang TIME         -- 16:00:00, 00:00:00, 08:00:00
);
```

### C. Tabel `roster_jadwal` (Jembatan Konteks Harian) - *CRITICAL*
Tabel ini menentukan kacamata logika mana yang dipakai sistem saat karyawan melakukan absen pada tanggal tertentu.
```sql
CREATE TABLE roster_jadwal (
    id_jadwal INT PRIMARY KEY AUTO_INCREMENT,
    id_karyawan INT,
    tanggal DATE NOT NULL,
    tipe_pekerjaan ENUM('PABRIK', 'DESIGN') NOT NULL,
    id_shift_pabrik INT NULL,
    durasi_kontrak_design INT NULL, -- 6 atau 8
    FOREIGN KEY (id_karyawan) REFERENCES karyawan(id_karyawan),
    FOREIGN KEY (id_shift_pabrik) REFERENCES master_shift_pabrik(id_shift),
    -- Mencegah duplikasi jadwal yang sama untuk satu karyawan pada hari yang sama
    UNIQUE KEY uq_jadwal_harian (id_karyawan, tanggal, tipe_pekerjaan)
);
```

### D. Tabel `log_absen` (Data Transaksi Absen)
```sql
CREATE TABLE log_absen (
    id_absen INT PRIMARY KEY AUTO_INCREMENT,
    id_jadwal INT NOT NULL,
    jam_masuk_aktual TIMESTAMP NULL,
    jam_pulang_aktual TIMESTAMP NULL,
    FOREIGN KEY (id_jadwal) REFERENCES roster_jadwal(id_jadwal)
);
```

---

## 3. Logika Pemrosesan Aplikasi (Backend Pseudo-code)

Ketika karyawan melakukan *Clock In/Out*, sistem wajib membaca data `roster_jadwal` hari itu untuk menentukan rumus perhitungan.

```python
def hitung_absen_harian(id_jadwal):
    jadwal = query_database("SELECT * FROM roster_jadwal WHERE id_jadwal = :id_jadwal", id_jadwal)
    log = query_database("SELECT * FROM log_absen WHERE id_jadwal = :id_jadwal", id_jadwal)
    
    durasi_kerja_menit = 0
    keterlambatan_menit = 0
    overtime_menit = 0
    
    # Hitung durasi mentah dalam menit
    durasi_aktual = (log.jam_pulang_aktual - log.jam_masuk_aktual).total_seconds() / 60
    
    if jadwal.tipe_pekerjaan == "PABRIK":
        shift = query_database("SELECT * FROM master_shift_pabrik WHERE id_shift = :id", jadwal.id_shift_pabrik)
        
        # 1. Logika Keterlambatan (Hanya ada di Pabrik)
        # Ambil bagian komponen waktu saja untuk dibandingkan dengan target shift masuk
        if log.jam_masuk_aktual.time() > shift.jam_masuk:
            keterlambatan_menit = hitung_selisih_menit(log.jam_masuk_aktual.time(), shift.jam_masuk)
            
        # 2. Durasi Kerja Efektif Pabrik (Kaku berdasarkan shift)
        durasi_kerja_menit = 480 # Standar 8 Jam (480 menit) minus keterlambatan jika regulasi ketat
        
        # 3. Lembur (Overtime) Pabrik
        if log.jam_pulang_aktual.time() > shift.jam_pulang:
            overtime_menit = hitung_selisih_menit(log.jam_pulang_aktual.time(), shift.jam_pulang)
            
    elif jadwal.tipe_pekerjaan == "DESIGN":
        target_menit = jadwal.durasi_kontrak_design * 60
        
        # 1. Logika Keterlambatan: TIDAK ADA (Jam fleksibel)
        keterlambatan_menit = 0
        
        # 2. Durasi Kerja Efektif Design: Berbasis stopwatch kumulatif
        if durasi_aktual >= target_menit:
            durasi_kerja_menit = target_menit
            # 3. Lembur (Overtime) Design: Kelebihan dari durasi kontrak harian
            overtime_menit = durasi_aktual - target_menit
        else:
            durasi_kerja_menit = durasi_aktual # Kurang dari kontrak, dihitung prorata
            overtime_menit = 0

    return {
        "durasi_efektif": durasi_kerja_menit,
        "keterlambatan": keterlambatan_menit,
        "overtime": overtime_menit
    }
```

---

## 4. Integrasi ke Jurnal Akuntansi (Double-Entry Posting)

Setiap hasil konversi dari fungsi di atas harus diposting ke modul akuntansi secara otomatis saat proses *payroll* dijalankan atau saat akhir hari (*End of Day processing*).

### Aturan Alokasi Cost Center:
* **Tipe PABRIK:** Masuk ke akun **Beban Tenaga Kerja Langsung / Pabrik** (Komponen Harga Pokok Produksi / HPP).
* **Tipe DESIGN:** Masuk ke akun **Beban Gaji Kreatif / Operasional** (Komponen Biaya Usaha).

### Contoh Entri Jurnal Otomatis (Saat Payroll / Clocks Terhitung):
* Kasus Ani di **Hari Pabrik** (Tarif misal Rp20.000 / Jam Efektif):
  * **[DEBIT]** `5-1001` Beban Tenaga Kerja Langsung Pabrik — Rp160.000
  * **[KREDIT]** `2-1001` Utang Gaji Karyawan — Rp160.000

* Kasus Ani di **Hari Design** (Tarif misal Rp25.000 / Jam Efektif):
  * **[DEBIT]** `5-1002` Beban Gaji Divisi Kreatif — Rp200.000
  * **[KREDIT]** `2-1001` Utang Gaji Karyawan — Rp200.000

---

## 5. Panduan UI/UX Aplikasi Karyawan

Aplikasi *front-end* harus bersifat dinamis mengikuti data dari `roster_jadwal`:
1. **Hari Pabrik:** UI menampilkan teks *"Jadwal Anda Hari Ini: Shift 1 (08.00 - 16.00)"*. Tombol absen memberikan validasi radius lokasi pabrik (jika ada).
2. **Hari Design:** UI menampilkan teks *"Jadwal Anda Hari Ini: Project Design - Kontrak [6/8] Jam"*. Tombol absen berubah fungsi menjadi pemicu *Stopwatch Aktif* yang menghitung mundur sisa kewajiban jam kerja karyawan.
3. **Kasus Multi-Roster (Double Job dalam 1 Hari):** Jika karyawan memiliki 2 jadwal di hari yang sama, tampilkan list kartu tugas. Karyawan harus melakukan *Clock Out* pada tugas pertama sebelum tombol *Clock In* pada tugas kedua diaktifkan oleh sistem.