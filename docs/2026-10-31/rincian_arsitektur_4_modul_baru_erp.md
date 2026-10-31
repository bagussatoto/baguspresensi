# 📦 Rincian Lengkap Arsitektur 4 Modul Baru ERP DDD Pragmatis

**Tanggal Pembuatan:** 31 Oktober 2026  
**Status:** Terimplementasi & Teruji (100% Green)  
**Target:** Laravel 11/12/13 | PHP 8.3+ | PostgreSQL 16  
**Modul yang Ditambahkan:** `Modules/Kas`, `Modules/Pembelian`, `Modules/Distribusi`, `Modules/Penjualan`

---

## 1. 🧭 Pohon Berkas Lengkap 4 Modul Baru

```
Modules/
├── Kas/                                   <-- 1. FONDASI MONETER & KASIR
│   ├── app/
│   │   ├── Application/
│   │   │   ├── Http/Controllers/
│   │   │   │   ├── KasAccountController.php
│   │   │   │   ├── KasKeluarController.php
│   │   │   │   ├── KasMasukController.php
│   │   │   │   └── KasMutasiController.php
│   │   │   └── Requests/
│   │   │       ├── StoreKasAccountRequest.php
│   │   │       ├── StoreKasKeluarRequest.php
│   │   │       ├── StoreKasMasukRequest.php
│   │   │       └── StoreKasMutasiRequest.php
│   │   ├── Domain/
│   │   │   ├── Contexts/
│   │   │   │   ├── KasKeluarContext.php
│   │   │   │   ├── KasMasukContext.php
│   │   │   │   └── KasMutasiContext.php
│   │   │   ├── Enums/
│   │   │   │   ├── KasAccountType.php
│   │   │   │   ├── KasTransactionType.php
│   │   │   │   └── PaymentMethodType.php
│   │   │   ├── Exceptions/
│   │   │   │   ├── ClosedShiftException.php
│   │   │   │   └── InsufficientCashBalanceException.php
│   │   │   ├── Models/
│   │   │   │   ├── KasAccount.php
│   │   │   │   ├── KasTransaction.php
│   │   │   │   ├── KasTransactionLine.php
│   │   │   │   └── KasTransfer.php
│   │   │   └── Services/
│   │   │       ├── KasKeluarService.php
│   │   │       ├── KasMasukService.php
│   │   │       └── KasMutasiService.php
│   │   └── Providers/
│   │       └── KasServiceProvider.php
│   ├── database/
│   │   └── migrations/
│   │       ├── 2026_10_31_160001_create_kas_accounts_table.php
│   │       ├── 2026_10_31_160002_create_kas_transactions_table.php
│   │       ├── 2026_10_31_160003_create_kas_transaction_lines_table.php
│   │       └── 2026_10_31_160004_create_kas_transfers_table.php
│   └── routes/
│       └── api.php
│
├── Pembelian/                             <-- 2. PENGADAAN & PENERIMAAN FISIK
│   ├── app/
│   │   ├── Application/
│   │   │   ├── Http/Controllers/
│   │   │   │   ├── GoodsReceiptController.php
│   │   │   │   ├── PurchaseBillController.php
│   │   │   │   └── PurchaseOrderController.php
│   │   │   └── Requests/
│   │   │       ├── StoreGoodsReceiptRequest.php
│   │   │       └── StorePurchaseOrderRequest.php
│   │   ├── Domain/
│   │   │   ├── Contexts/
│   │   │   │   ├── GoodsReceiptContext.php
│   │   │   │   └── PurchaseOrderContext.php
│   │   │   ├── Enums/
│   │   │   │   ├── PurchaseBillStatus.php
│   │   │   │   └── PurchaseOrderStatus.php
│   │   │   ├── Models/
│   │   │   │   ├── GoodsReceipt.php
│   │   │   │   ├── GoodsReceiptItem.php
│   │   │   │   ├── PurchaseBill.php
│   │   │   │   ├── PurchaseBillItem.php
│   │   │   │   ├── PurchaseOrder.php
│   │   │   │   └── PurchaseOrderItem.php
│   │   │   ├── Pipelines/
│   │   │   │   ├── IncrementWarehouseStockPipe.php
│   │   │   │   ├── RecalculateMovingAverageHppPipe.php
│   │   │   │   └── ValidatePurchaseOrderPipe.php
│   │   │   └── Services/
│   │   │       └── PurchaseOrderOrchestrator.php
│   │   ├── Events/
│   │   │   ├── GoodsReceivedEvent.php
│   │   │   └── PurchaseBillIssuedEvent.php
│   │   └── Providers/
│   │       └── PembelianServiceProvider.php
│   ├── database/
│   │   └── migrations/
│   │       ├── 2026_10_31_170001_create_purchase_orders_tables.php
│   │       ├── 2026_10_31_170002_create_goods_receipts_tables.php
│   │       └── 2026_10_31_170003_create_purchase_bills_tables.php
│   └── routes/
│       └── api.php
│
├── Distribusi/                            <-- 3. LOGISTIK & MUTASI ANTAR-CABANG
│   ├── app/
│   │   ├── Application/
│   │   │   ├── Http/Controllers/
│   │   │   │   ├── DeliveryOrderController.php
│   │   │   │   └── StockTransferController.php
│   │   │   └── Requests/
│   │   │       ├── StoreDeliveryOrderRequest.php
│   │   │       ├── StoreStockTransferDispatchRequest.php
│   │   │       └── StoreStockTransferReceiveRequest.php
│   │   ├── Domain/
│   │   │   ├── Contexts/
│   │   │   │   ├── StockTransferDispatchContext.php
│   │   │   │   └── StockTransferReceiveContext.php
│   │   │   ├── Enums/
│   │   │   │   └── TransferStatus.php
│   │   │   ├── Models/
│   │   │   │   ├── DeliveryOrder.php
│   │   │   │   ├── DeliveryOrderItem.php
│   │   │   │   ├── StockTransfer.php
│   │   │   │   └── StockTransferItem.php
│   │   │   ├── Pipelines/
│   │   │   │   ├── DispatchProcessing/
│   │   │   │   │   ├── AddInTransitStockPipe.php
│   │   │   │   │   └── DeductSourceStockPipe.php
│   │   │   │   └── ReceiveProcessing/
│   │   │   │       ├── AddDestinationStockPipe.php
│   │   │   │       └── DeductInTransitStockPipe.php
│   │   │   └── Services/
│   │   │       └── StockTransferService.php
│   │   ├── Events/
│   │   │   ├── DeliveryDispatchedEvent.php
│   │   │   ├── TransferDispatchedEvent.php
│   │   │   └── TransferReceivedEvent.php
│   │   └── Providers/
│   │       └── DistribusiServiceProvider.php
│   ├── database/
│   │   └── migrations/
│   │       ├── 2026_10_31_180001_create_stock_transfers_tables.php
│   │       └── 2026_10_31_180002_create_delivery_orders_tables.php
│   └── routes/
│       └── api.php
│
└── Penjualan/                             <-- 4. POS KASIR & PENJUALAN B2B KREDIT
    ├── app/
    │   ├── Application/
    │   │   ├── Http/Controllers/
    │   │   │   ├── PosCheckoutController.php
    │   │   │   ├── PosShiftController.php
    │   │   │   └── SalesOrderController.php
    │   │   └── Requests/
    │   │       ├── ClosePosShiftRequest.php
    │   │       ├── StorePosCheckoutRequest.php
    │   │       ├── StorePosShiftOpenRequest.php
    │   │       └── StoreSalesOrderRequest.php
    │   ├── Domain/
    │   │   ├── Contexts/
    │   │   │   ├── PosCheckoutContext.php
    │   │   │   ├── PosShiftContext.php
    │   │   │   └── SalesOrderContext.php
    │   │   ├── Enums/
    │   │   │   ├── SalesOrderStatus.php
    │   │   │   └── ShiftStatus.php
    │   │   ├── Models/
    │   │   │   ├── PosOrder.php
    │   │   │   ├── PosOrderItem.php
    │   │   │   ├── PosShift.php
    │   │   │   ├── SalesInvoice.php
    │   │   │   ├── SalesInvoiceItem.php
    │   │   │   ├── SalesOrder.php
    │   │   │   └── SalesOrderItem.php
    │   │   ├── Pipelines/
    │   │   │   ├── CalculatePricingAndTaxPipe.php
    │   │   │   ├── FreezeJsonbSnapshotPipe.php
    │   │   │   ├── GenerateAtomicInvoiceNumberPipe.php
    │   │   │   ├── ValidateCustomerCreditPipe.php
    │   │   │   └── ValidateStockAvailabilityPipe.php
    │   │   └── Services/
    │   │       ├── PosShiftService.php
    │   │       └── SalesOrderOrchestrator.php
    │   ├── Events/
    │   │   ├── PosOrderCompletedEvent.php
    │   │   └── SalesInvoiceIssuedEvent.php
    │   └── Providers/
    │       └── PenjualanServiceProvider.php
    ├── database/
    │   └── migrations/
    │       ├── 2026_10_31_190001_create_pos_shifts_tables.php
    │       ├── 2026_10_31_190002_create_pos_orders_tables.php
    │       ├── 2026_10_31_190003_create_sales_orders_tables.php
    │       └── 2026_10_31_190004_create_sales_invoices_tables.php
    └── routes/
        └── api.php
```

---

## 2. 🔍 Rincian Teknis per Modul

### 2.1. Modul `Kas` (`Modules/Kas/`)
* **Tujuan:** Mengelola kasir toko (laci uang), kas operasional harian (*petty cash*), dan rekening bank internal.
* **Tabel PostgreSQL:**
  * `kas_accounts`: Master akun kas/bank per cabang.
  * `kas_transactions`: Jurnal transaksi masuk/keluar atomik dengan format nomor `KM-` atau `KK-` dan audit snapshot payload (JSONB).
  * `kas_transaction_lines`: Alokasi baris faktur piutang/hutang.
  * `kas_transfers`: Mutasi pemindahan saldo antar-rekening internal beserta biaya admin.
* **Logika Inti (`Services`):**
  * `KasMasukService`: Menerima uang, menambah saldo, dan **secara atomik memanggil `CustomerCreditGuard::releaseCredit()`** jika menerima pembayaran piutang dari konsumen.
  * `KasKeluarService`: Memvalidasi ketersediaan dana dan memotong saldo dengan `lockForUpdate()`. Melempar `InsufficientCashBalanceException` jika saldo tidak mencukupi.
  * `KasMutasiService`: Mengunci ID akun secara terurut (*sorted locking*) untuk menjamin transaksi bebas dari *deadlock*, memotong akun asal, menambah akun tujuan, dan mencatat jurnal ganda `MUT-OUT` dan `MUT-IN`.

---

### 2.2. Modul `Pembelian` (`Modules/Pembelian/`)
* **Tujuan:** Mengelola rantai pasok pengadaan barang (*procurement*), penerimaan fisik gudang, dan hutang dagang (*Accounts Payable*).
* **Tabel PostgreSQL:**
  * `purchase_orders` & `purchase_order_items`: PO pembelian dari supplier (`draft`, `approved`, `partial`, `closed`).
  * `goods_receipts` & `goods_receipt_items`: Surat penerimaan barang fisik di gudang.
  * `purchase_bills` & `purchase_bill_items`: Tagihan hutang pembelian (AP) dengan tanggal jatuh tempo.
* **Logika Inti (`Pipelines` & `Orchestrator`):**
  * `ValidatePurchaseOrderPipe`: Memastikan supplier aktif dan qty penerimaan tidak melebihi sisa PO.
  * `IncrementWarehouseStockPipe`: Menambah stok fisik gudang pada tabel `stock_inventories`.
  * `RecalculateMovingAverageHppPipe`: Memperbarui nilai HPP bergerak (*Moving Average*) sesuai PSAK 14 pada atribut `cost_price` varian produk:
    $$\text{HPP Baru} = \frac{(\text{Stok Lama} \times \text{HPP Lama}) + (\text{Qty Masuk} \times \text{Harga Beli})}{\text{Stok Lama} + \text{Qty Masuk}}$$
  * `PurchaseOrderOrchestrator`: Mengkoordinasikan alur approval PO, penerimaan barang, dan penerbitan otomatis tagihan `PurchaseBill` yang siap dilunasi via Modul Kas.

---

### 2.3. Modul `Distribusi` (`Modules/Distribusi/`)
* **Tujuan:** Mengelola transfer barang antar-cabang/gudang dan pengiriman barang ke konsumen (*logistics*).
* **Tabel PostgreSQL:**
  * `stock_transfers` & `stock_transfer_items`: Dokumen transfer barang antar-cabang.
  * `delivery_orders` & `delivery_order_items`: Surat Jalan pengiriman pesanan ke konsumen (PSAK 72).
* **Two-Step Logistics Handshake:**
  * **Langkah 1 (Dispatch / Pengiriman):**
    * Pipa `DeductSourceStockPipe` memotong stok fisik dari gudang cabang asal.
    * Pipa `AddInTransitStockPipe` memasukkan stok tersebut ke gudang virtual bertipe `in_transit`. Status transfer: `InTransit`.
  * **Langkah 2 (Receive / Penerimaan di Tujuan):**
    * Pipa `DeductInTransitStockPipe` memotong stok dari gudang *In-Transit*.
    * Pipa `AddDestinationStockPipe` menambahkan stok fisik ke gudang cabang tujuan.
    * Jika qty fisik yang diterima tidak sama dengan yang dikirim (misal ada barang rusak/hilang di jalan), status transfer otomatis menjadi **`Selisih`**.
* **Logika Inti (`Services`):**
  * `StockTransferService`: Mengatur proses pengiriman, penerimaan, dan pembuatan Surat Jalan (`createDeliveryOrder`) dengan pemotongan stok otomatis.

---

### 2.4. Modul `Penjualan` (`Modules/Penjualan/`)
* **Tujuan:** Melayani penjualan retail kasir POS dan transaksi kredit B2B (*Accounts Receivable*).
* **Tabel PostgreSQL:**
  * `pos_shifts`: Sesi buka/tutup laci kasir dengan modal awal, expected cash, pencatatan fisik kasir (*Blind Count*), dan audit selisih kas.
  * `pos_orders` & `pos_order_items`: Nota penjualan kasir lunas beserta rincian kembalian uang.
  * `sales_orders` & `sales_order_items`: Pesanan penjualan B2B kredit.
  * `sales_invoices` & `sales_invoice_items`: Faktur tagihan piutang konsumen (AR).
* **Logika Inti (`Pipelines` & `Services`):**
  * `ValidateCustomerCreditPipe`: Memvalidasi batas plafon kredit konsumen via `CustomerCreditGuard` sebelum SO dibuat.
  * `ValidateStockAvailabilityPipe`: Memastikan stok fisik di gudang mencukupi sebelum transaksi diproses.
  * `CalculatePricingAndTaxPipe`: Menghitung diskon, DPP, dan PPN 11% (pembulatan ke bawah / *floor* sesuai aturan resmi DJP Coretax).
  * `GenerateAtomicInvoiceNumberPipe`: Menghasilkan nomor nota/faktur berurutan dengan locking atomik (`INV-{BRANCH}-{Ymd}-{SEQ}`).
  * `FreezeJsonbSnapshotPipe`: Membekukan salinan lengkap data master konsumen dan produk ke format JSONB untuk integritas audit ISO 27001.
  * `PosShiftService`: Mengatur pembukaan shift, penutupan shift kasir dengan *blind count*, dan verifikasi rekonsiliasi kasir oleh supervisor.
  * `SalesOrderOrchestrator`:
    * POS Checkout: Memotong stok gudang real-time dan **otomatis menyetor uang ke akun kas laci kasir** melalui `KasMasukService`.
    * SO B2B: Menerbitkan faktur tagihan penjualan yang memotong stok dan mengurangi plafon kredit konsumen. Saat faktur dilunasi melalui `KasMasukService`, plafon kredit konsumen otomatis pulih kembali!

---

## 3. 🧪 Verifikasi Pengujian Otomatis (PHPUnit)

Seluruh 4 modul baru diuji secara komprehensif melalui test suite automated berikut:

| Nama File Test | Modul | Jumlah Test | Status |
| :--- | :--- | :---: | :---: |
| [`tests/Feature/KasModuleTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/KasModuleTest.php) | `Kas` | 7 Tests | ✅ PASSED |
| [`tests/Feature/KasApiTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/KasApiTest.php) | `Kas` | 4 Tests | ✅ PASSED |
| [`tests/Feature/PembelianModuleTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/PembelianModuleTest.php) | `Pembelian` | 4 Tests | ✅ PASSED |
| [`tests/Feature/PembelianApiTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/PembelianApiTest.php) | `Pembelian` | 2 Tests | ✅ PASSED |
| [`tests/Feature/DistribusiModuleTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/DistribusiModuleTest.php) | `Distribusi` | 5 Tests | ✅ PASSED |
| [`tests/Feature/PenjualanModuleTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/PenjualanModuleTest.php) | `Penjualan` | 5 Tests | ✅ PASSED |
| **Total Test Suites Baru** | **4 Modul** | **27 Tests** | **✅ 100% GREEN** |

---

## 4. 🔗 Registrasi Service Provider

Keempat modul baru telah terhubung secara resmi ke bootstrap aplikasi Laravel pada [`bootstrap/providers.php`](file:///var/www/laravel_apps/bagus/bootstrap/providers.php):

```php
return [
    App\Providers\AppServiceProvider::class,
    Modules\MasterData\Providers\MasterDataServiceProvider::class,
    Modules\Kas\Providers\KasServiceProvider::class,
    Modules\Pembelian\Providers\PembelianServiceProvider::class,
    Modules\Distribusi\Providers\DistribusiServiceProvider::class,
    Modules\Penjualan\Providers\PenjualanServiceProvider::class,
];
```
