# 📱 Panduan Integrasi API Presensi & Cuti (V1)
**Maya Graha Kencana (MGK) — Operational Attendance System**  
**Versi API:** `v1`  
**Base URL:** `http://<server-host>/api/v1`  
**Format Payload:** `application/json` atau `multipart/form-data` (untuk upload file)  
**Dokumentasi Terkait:** [docs/roadmap_presensi.md](file:///var/www/laravel_apps/bagus/docs/roadmap_presensi.md) & [docs/tahapan_eksekusi_presensi.md](file:///var/www/laravel_apps/bagus/docs/tahapan_eksekusi_presensi.md)

---

## 1. Konsep & Aturan Bisnis (Core Rules)

1. **Work Date & Shift 24 Jam (Operasional Warnet & Toko):**
   - Warnet beroperasi 24 jam dengan 4 rotasi shift:
     - **Shift 1:** `01:00 - 07:00`
     - **Shift 2:** `07:00 - 13:00`
     - **Shift 3:** `13:00 - 19:00`
     - **Shift 4 (Lintas Malam):** `19:00 - 01:00`
     - **Shift BO (Back Office):** `08:30 - 17:30`
   - *Penting:* Karyawan Shift 4 yang masuk pukul 19:05 dan pulang pukul 01:15 dini hari keesokan harinya, catatan pulangnya **tetap terikat pada `work_date` tanggal masuk**. Hal ini mencegah tanggal presensi terbelah dua.

2. **Validasi Geofencing (GPS Radius):**
   - Presensi dihitung valid jika jarak koordinat karyawan ke titik GPS cabang &le; `radius_meters` cabang (standar 100 meter).
   - Perhitungan menggunakan formula *Haversine Great-Circle*. Jika karyawan di luar radius, API mengembalikan HTTP `422 Unprocessable Entity` beserta detail jarak riil.

3. **Grace Period & Keterlambatan:**
   - Standar toleransi keterlambatan adalah 15 menit. Masuk setelah `start_time + 15 menit` otomatis berstatus `late` dan mencatat `late_minutes`.

4. **Anti-Fraud & Keamanan Lokasi:**
   - Deteksi *Mock Location Provider / Fake GPS*: Request ditolak dengan HTTP `403 Forbidden`.
   - Deteksi *Teleportasi Mustahil*: Kecepatan perpindahan koordinat > 350 km/jam dari lokasi clock-in/out terakhir ditolak HTTP `403 Forbidden`.

5. **Foto Selfie:**
   - Mendukung format file gambar langsung (JPEG, PNG, WebP) maupun string **Base64 Data URL** (`data:image/jpeg;base64,...`).

---

## 2. Header Standar

Semua HTTP Request wajib menyertakan header:
```http
Accept: application/json
Content-Type: application/json
```
*(Gunakan `Content-Type: multipart/form-data` jika mengirim file gambar selfie secara binary).*

---

## 3. Daftar Endpoint API

### 🔹 Master Data Shift
#### `GET /api/v1/shifts`
Mengambil daftar shift kerja yang aktif di sistem.

**Contoh Response (HTTP 200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "code": "SHIFT-1",
      "name": "Shift 1 (Dini Hari)",
      "start_time": "01:00:00",
      "end_time": "07:00:00",
      "is_overnight": false,
      "grace_period_minutes": 15,
      "is_active": true
    },
    {
      "id": 4,
      "code": "SHIFT-4",
      "name": "Shift 4 (Malam - Lintas Hari)",
      "start_time": "19:00:00",
      "end_time": "01:00:00",
      "is_overnight": true,
      "grace_period_minutes": 15,
      "is_active": true
    }
  ]
}
```

---

### 🔹 Status Presensi Hari Ini
#### `GET /api/v1/attendance/today?employee_id={id}`
Mengecek apakah karyawan sudah clock-in atau clock-out pada hari operasional ini.

**Contoh Response (HTTP 200 OK):**
```json
{
  "has_clocked_in": true,
  "has_clocked_out": false,
  "data": {
    "id": 12,
    "employee": {
      "id": 1,
      "name": "Bagus Prayogo",
      "code": "OP-001"
    },
    "work_date": "2026-10-11",
    "shift": {
      "id": 2,
      "name": "Shift 2 (Pagi)"
    },
    "clock_in_at": "2026-10-11T07:05:00+07:00",
    "clock_out_at": null,
    "status": "present",
    "status_label": "Hadir Tepat Waktu",
    "late_minutes": 0,
    "duration_minutes": 0,
    "overtime_minutes": 0
  }
}
```

---

### 🔹 Clock-In (Scan Masuk)
#### `POST /api/v1/attendance/clock-in`
Mencatat kehadiran masuk karyawan berdasarkan validasi GPS dan foto selfie.

**Payload Request (JSON / FormData):**
| Field | Tipe | Wajib | Keterangan |
|---|---|:---:|---|
| `employee_id` | Integer | Ya | ID Karyawan |
| `branch_id` | Integer | Ya | ID Cabang tempat presensi |
| `shift_id` | Integer | Tidak | ID Shift (jika kosong, auto-detect berdasarkan jam saat ini) |
| `latitude` | Numeric | Ya | Koordinat lintang (-90 s/d 90) |
| `longitude` | Numeric | Ya | Koordinat bujur (-180 s/d 180) |
| `selfie_file` | File | Opsional* | File foto selfie (max 5MB) |
| `selfie_base64` | String | Opsional* | Alternatif string Base64 Data URL |
| `is_mock_location` | Boolean | Tidak | Flag indikator fake GPS dari OS (default `false`) |
| `device_info` | Object/String | Tidak | Informasi merk HP, OS, versi app |

*\*Catatan: Minimal salah satu dari `selfie_file` atau `selfie_base64` wajib dikirim.*

**Contoh Request Payload (JSON):**
```json
{
  "employee_id": 1,
  "branch_id": 1,
  "latitude": -6.200010,
  "longitude": 106.816670,
  "selfie_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
  "device_info": {
    "model": "Samsung Galaxy A54",
    "os": "Android 14",
    "app_version": "1.0.0"
  }
}
```

**Contoh Response Sukses (HTTP 201 Created):**
```json
{
  "message": "Presensi masuk berhasil dicatat.",
  "data": {
    "id": 14,
    "work_date": "2026-10-11",
    "status": "present",
    "status_label": "Hadir Tepat Waktu",
    "late_minutes": 0,
    "clock_in_at": "2026-10-11T07:05:12+07:00",
    "clock_in_distance_meters": 12.4
  }
}
```

---

### 🔹 Clock-Out (Scan Pulang)
#### `POST /api/v1/attendance/clock-out`
Mencatat kepulangan karyawan, menghitung durasi kerja, dan mengalkulasi menit lembur.

**Payload Request (JSON / FormData):**
| Field | Tipe | Wajib | Keterangan |
|---|---|:---:|---|
| `employee_id` | Integer | Ya | ID Karyawan |
| `latitude` | Numeric | Ya | Koordinat lintang |
| `longitude` | Numeric | Ya | Koordinat bujur |
| `selfie_file` / `selfie_base64` | File/Str | Tidak | Foto selfie saat pulang |
| `is_mock_location` | Boolean | Tidak | Flag fake GPS |

**Contoh Response Sukses (HTTP 200 OK):**
```json
{
  "message": "Presensi pulang berhasil dicatat.",
  "data": {
    "id": 14,
    "work_date": "2026-10-11",
    "clock_out_at": "2026-10-11T13:45:00+07:00",
    "duration_minutes": 400,
    "overtime_minutes": 45,
    "clock_out_distance_meters": 15.1
  }
}
```

---

### 🔹 Riwayat Presensi
#### `GET /api/v1/attendance/history`
Mengambil riwayat absensi karyawan dengan filter rentang tanggal.

**Query Parameters:**
- `employee_id` (Wajib): ID karyawan.
- `start_date` (Opsional): Format `YYYY-MM-DD` (default: awal bulan berjalan).
- `end_date` (Opsional): Format `YYYY-MM-DD` (default: hari ini).

---

### 🔹 Pengajuan Izin / Cuti
#### `POST /api/v1/leaves`
Mengajukan permohonan cuti tahunan, izin sakit, atau izin keperluan khusus.

**Payload Request:**
| Field | Tipe | Wajib | Nilai / Format |
|---|---|:---:|---|
| `employee_id` | Integer | Ya | ID Karyawan |
| `leave_type` | String | Ya | `annual`, `sick`, `permission`, `emergency` |
| `start_date` | Date | Ya | Format `YYYY-MM-DD` |
| `end_date` | Date | Ya | Format `YYYY-MM-DD` |
| `reason` | String | Ya | Alasan pengajuan |
| `attachment` | File | Tidak | Surat dokter / bukti pendukung (PDF, JPG, PNG) |

**Contoh Response Sukses (HTTP 201 Created):**
```json
{
  "message": "Permohonan cuti/izin berhasil diajukan dan menunggu persetujuan.",
  "data": {
    "id": 5,
    "leave_type": "annual",
    "start_date": "2026-10-15",
    "end_date": "2026-10-17",
    "total_days": 3,
    "status": "pending",
    "reason": "Keperluan keluarga di luar kota"
  }
}
```

---

### 🔹 Riwayat Pengajuan Cuti
#### `GET /api/v1/leaves?employee_id={id}`
Melihat daftar permohonan izin/cuti beserta status persetujuan (`pending`, `approved`, `rejected`) dan catatan penolakan jika ditolak.

---

## 4. HTTP Status Code & Error Handling

| HTTP Status | Penyebab & Arti |
|:---:|---|
| **200 OK** | Permintaan sukses (Clock-out, fetch data riwayat). |
| **201 Created** | Data baru berhasil dibuat (Clock-in, pengajuan cuti). |
| **400 Bad Request** | Request tidak valid atau alur salah (misal: belum clock-in tapi minta clock-out). |
| **403 Forbidden** | Terindikasi kecurangan / manipulasi lokasi (*Mock GPS* atau teleportasi mustahil). |
| **404 Not Found** | Data karyawan, shift, atau cabang tidak ditemukan. |
| **409 Conflict** | Duplikasi presensi (karyawan sudah clock-in pada tanggal dan shift yang sama). |
| **422 Unprocessable Entity** | Gagal validasi data form atau lokasi GPS berada di luar radius cabang yang ditentukan. |
| **500 Server Error** | Terjadi kegagalan internal sistem. |

---

## 5. Contoh Penanganan Error di Client (JavaScript / TypeScript)

```typescript
try {
  const response = await fetch('/api/v1/attendance/clock-in', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      employee_id: 1,
      branch_id: 1,
      latitude: -6.200010,
      longitude: 106.816670,
      selfie_base64: photoDataUrl,
    }),
  });

  const result = await response.json();

  if (!response.ok) {
    if (response.status === 422) {
      alert(`Gagal Geofencing: ${result.message}`);
    } else if (response.status === 409) {
      alert('Anda sudah melakukan scan masuk hari ini!');
    } else if (response.status === 403) {
      alert('Peringatan Keamanan: Terdeteksi fake GPS atau manipulasi lokasi!');
    } else {
      alert(`Terjadi kesalahan: ${result.message}`);
    }
    return;
  }

  console.log('Clock-in berhasil:', result.data);
} catch (error) {
  console.error('Network error:', error);
}
```
