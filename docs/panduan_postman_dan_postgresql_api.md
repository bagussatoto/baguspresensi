# 🚀 Panduan Postman & Integrasi PostgreSQL API

**Aplikasi:** Bagus (Sistem Presensi & Master Data Maya Graha Kencana)  
**Host URL:** `http://192.168.5.10:8086/bagus`  
**Database:** PostgreSQL (`pgsql`) — Host `192.168.5.10:5432`, DB: `bagus`  
**File Postman Collection:** [postman_collection.json](file:///var/www/laravel_apps/bagus/postman_collection.json)  
**URL Unduh Koleksi:** `http://192.168.5.10:8086/bagus/postman_collection.json`

---

## 1. Status Konfigurasi Sistem

| Komponen | Nilai Konfigurasi | Status | Keterangan |
| :--- | :--- | :--- | :--- |
| **Aplikasi Laravel** | `v12.x` (PHP 8.5) | **Aktif** | Berjalan di port 8086 under path `/bagus` |
| **Database Server** | PostgreSQL (`pgsql`) | **OK / Terhubung** | Host `192.168.5.10`, Port `5432`, DB `bagus` |
| **API Base URL** | `http://192.168.5.10:8086/bagus/api/v1` | **Aktif** | RESTful JSON API |
| **Postman Ready** | `postman_collection.json` | **Tersedia** | Skema Collection v2.1.0 |

---

## 2. Cara Import Koleksi ke Postman

### Metode A: Import Langsung via Link (Direkomendasikan)
1. Buka aplikasi **Postman**.
2. Klik menu **Import** di kiri atas.
3. Masukkan URL berikut pada input link:
   ```text
   http://192.168.5.10:8086/bagus/postman_collection.json
   ```
4. Klik **Import**. Seluruh folder dan endpoint langsung tersedia di workspace Postman Anda.

### Metode B: Import dari File Fisik
1. Unduh file `postman_collection.json` dari link: `http://192.168.5.10:8086/bagus/postman_collection.json`.
2. Di Postman, klik **Import** &rarr; **Files** &rarr; pilih file yang telah diunduh.

---

## 3. Variabel Bawaan Koleksi (Collection Variables)

Koleksi ini sudah dilengkapi variabel bawaan sehingga tidak perlu mengganti URL atau ID berulang kali:

| Nama Variabel | Nilai Default | Penjelasan |
| :--- | :--- | :--- |
| `{{baseUrl}}` | `http://192.168.5.10:8086/bagus` | Alamat host server dan subpath |
| `{{employeeId}}` | `54` | ID Karyawan contoh (*Yulk*) |
| `{{branchId}}` | `3` | ID Kantor Cabang (*Maya Graha Kencana*) |
| `{{latitude}}` | `-6.1585` | Titik koordinat GPS valid cabang |
| `{{longitude}}` | `106.9080` | Titik koordinat GPS valid cabang |

---

## 4. Rincian Endpoint API

### Folder 01: Presensi & Kehadiran (API v1)

#### 1.1. Master Data Shift
* **Method:** `GET`
* **URL:** `{{baseUrl}}/api/v1/attendance/shifts`
* **Header:** `Accept: application/json`
* **Response Contoh:**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "code": "SHIFT-1",
        "name": "Shift 1 (Dini Hari)",
        "start_time": "01:00:00",
        "end_time": "07:00:00"
      }
    ]
  }
  ```

#### 1.2. Presensi Masuk (Clock In)
* **Method:** `POST`
* **URL:** `{{baseUrl}}/api/v1/attendance/clock-in`
* **Header:** `Accept: application/json`, `Content-Type: application/json`
* **Request Body:**
  ```json
  {
    "employee_id": {{employeeId}},
    "branch_id": {{branchId}},
    "latitude": {{latitude}},
    "longitude": {{longitude}},
    "shift_id": null,
    "is_mock_location": false,
    "notes": "Presensi masuk via Postman"
  }
  ```

#### 1.3. Presensi Pulang (Clock Out)
* **Method:** `POST`
* **URL:** `{{baseUrl}}/api/v1/attendance/clock-out`
* **Header:** `Accept: application/json`, `Content-Type: application/json`
* **Request Body:**
  ```json
  {
    "employee_id": {{employeeId}},
    "branch_id": {{branchId}},
    "latitude": {{latitude}},
    "longitude": {{longitude}},
    "is_mock_location": false,
    "notes": "Presensi pulang via Postman"
  }
  ```

#### 1.4. Cek Status Hari Ini
* **Method:** `GET`
* **URL:** `{{baseUrl}}/api/v1/attendance/today?employee_id={{employeeId}}`

#### 1.5. Riwayat Kehadiran (Pagination)
* **Method:** `GET`
* **URL:** `{{baseUrl}}/api/v1/attendance/history?employee_id={{employeeId}}&month=10&year=2026`

---

### Folder 02: Cuti & Izin (API v1)

#### 2.1. Pengajuan Cuti / Izin
* **Method:** `POST`
* **URL:** `{{baseUrl}}/api/v1/leave/request`
* **Request Body:**
  ```json
  {
    "employee_id": {{employeeId}},
    "leave_type": "annual",
    "start_date": "2026-10-20",
    "end_date": "2026-10-21",
    "reason": "Keperluan keluarga"
  }
  ```
  *(Pilihan `leave_type`: `annual`, `sick`, `permission`, `emergency`).*

#### 2.2. Riwayat Cuti / Izin
* **Method:** `GET`
* **URL:** `{{baseUrl}}/api/v1/leave/history?employee_id={{employeeId}}`

---

### Folder 03 - 05: Master Data (Customers, Suppliers, Pricelists)

* **List Customers:** `GET {{baseUrl}}/api/master-data/customers`
* **Create Customer:** `POST {{baseUrl}}/api/master-data/customers`
* **List Suppliers:** `GET {{baseUrl}}/api/master-data/suppliers`
* **List Pricelists:** `GET {{baseUrl}}/api/master-data/pricelists`
* **Simulasi Harga:** `POST {{baseUrl}}/api/master-data/simulate-pricing`

---

## 5. Catatan Error & Validasi

1. **`OUTSIDE_GEOFENCE` (HTTP 422):**
   * Terjadi jika koordinat `latitude` dan `longitude` berada di luar radius cabang (standar radius 100 meter dari kantor MGK).
2. **`ALREADY_CLOCKED_IN` (HTTP 409):**
   * Karyawan sudah melakukan clock in pada shift/tanggal kerja aktif hari ini.
3. **`MOCK_LOCATION_DETECTED` (HTTP 403):**
   * Parameter `is_mock_location` bernilai `true` atau terindikasi fake GPS.
