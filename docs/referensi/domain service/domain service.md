# Arsitektur ERP: Application Service / Action Class sebagai Orchestrator

Dokumen ini menjelaskan peran dan implementasi **Action Class** (Application Service) dalam arsitektur DDD Pragmatis pada proyek Laravel ERP. 

## Prinsip Utama
* **Single Responsibility:** Satu Action Class hanya melakukan satu tugas spesifik (misal: `CreateInvoiceAction`, `ProcessPayrollAction`).
* **Orchestrator Only:** Action bertugas mengatur alur eksekusi (*workflow*). Action **TIDAK BOLEH** mengandung rumus kalkulasi atau logika bisnis inti.
* **Delegasi:** Action memanggil *Domain Service* untuk perhitungan lintas entitas dan *Domain Model* untuk perhitungan internal entitas.

---

## Alur Data & Eksekusi

```text
[HTTP Request / Job]
        │
        ▼
   [Controller]  <-- Memvalidasi HTTP & input mentah
        │
        ▼
    [Action]     <-- (Orchestrator) Mengambil data & mengatur alur
   ├───┼───┐
   │   │   └──► [Domain Service] <-- Mengeksekusi kalkulasi kompleks / lintas domain
   │   └──────► [Domain Model]   <-- Mengeksekusi kalkulasi internal & mutasi data
   ▼   ▼
[DB / Repository] <-- Menyimpan hasil akhir yang sudah matang
```

---

## Contoh Implementasi: Pembuatan Invoice ERP

Berikut adalah contoh kasus nyata pembuatan Invoice yang membutuhkan kalkulasi pajak (via Domain Service) dan kalkulasi total item (via Domain Model).

### 1. Struktur Folder (Domain-Driven)
```text
app/
└── Domain/
    └── Invoicing/
        ├── Actions/
        │   └── CreateInvoiceAction.php  <-- Poin No. 3 (Orchestrator)
        ├── Models/
        │   ├── Invoice.php              <-- Domain Model (Rich Model)
        │   └── InvoiceItem.php
        └── Services/
            └── TaxCalculationService.php <-- Domain Service (Kalkulasi Kompleks)
```

### 2. Kode Program

#### A. Action Class (The Orchestrator)
Perhatikan bagaimana kelas ini bersih dari rumus matematika. Tugasnya hanya menghubungkan model, service, dan menyimpan data.

```php
namespace App\Domain\Invoicing\Actions;

use App\Domain\Invoicing\Models\Invoice;
use App\Domain\Invoicing\Services\TaxCalculationService;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    protected TaxCalculationService \$taxService;

    // Dependency Injection untuk memasukkan Domain Service
    public function __construct(TaxCalculationService \$taxService)
    {
        this->taxService = taxService;
    }

    public function execute(array \$data): Invoice
    {
        return DB::transaction(function () use (\(data) { // 1. Buat instance Invoice awal (Draft)\)invoice = Invoice::create([
                'customer_id' => \$data['customer_id'],
                'status' => 'draft',
            ]);

            // 2. Masukkan item-item invoice
            foreach (\$data['items'] as \(item) {\)invoice->items()->create([
                    'product_id' => \$item['product_id'],
                    'quantity' => \$item['quantity'],
                    'price' => \$item['price'],
                ]);
            }

            // 3. Panggil Model untuk kalkulasi internal (Subtotal)
            subTotal = invoice->calculateSubTotal();

            // 4. Panggil Domain Service untuk kalkulasi lintas domain/aturan pajak
            taxAmount = this->taxService->calculateValueAddedTax(subTotal, data['tax_zone_id']);

            // 5. Update hasil akhir ke database
            \$invoice->update([
                'sub_total' => \$subTotal,
                'tax_amount' => \$taxAmount,
                'grand_total' => subTotal + taxAmount,
                'status' => 'finalized',
            ]);

            return \$invoice->refresh();
        });
    }
}
```

#### B. Controller (Hanya Memicu Action)
Controller tetap *skinny* (tipis) karena beban kerja dialihkan ke Action.

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Invoicing\Actions\CreateInvoiceAction;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function store(Request request, CreateInvoiceAction action)
    {
        // Validasi input HTTP
        validated = request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tax_zone_id' => 'required|integer',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|integer',
        ]);

        // Eksekusi Action
        \$invoice = action->execute(validated);

        return response()->json([
            'message' => 'Invoice berhasil dibuat',
            'data' => \$invoice
        ], 201);
    }
}
```

---

## Keuntungan Pendekatan Ini dalam ERP
1. **Reusability Tinggi:** Jika kalkulasi pembuatan invoice ini ingin dipicu dari *Background Job*, *Command Line (Artisan)*, atau *Webhook API*, Anda cukup memanggil `CreateInvoiceAction` tanpa perlu menulis ulang logika kalkulasi.
2. **Kemudahan Unit Testing:** Anda dapat menguji fungsionalitas alur tanpa terikat dengan HTTP Request.
3. **Pemisahan Perhatian (Separation of Concerns):** Programmer yang membaca kode langsung paham alur proses bisnis hanya dengan membaca isi *Action Class*.
