# Dokumentasi Implementasi Laravel DDD Pragmatis: MDM ERP Multi-Cabang

Dokumen ini menjelaskan penerapan struktur database Master Data Management (MDM) Multi-Cabang + DC ke dalam **Laravel** menggunakan pendekatan **Domain-Driven Design (DDD) Pragmatis**. 

---

## 1. Struktur Direktori Project (Bounded Context)

```text
app/
├── Domains/
│   ├── Organization/              <-- Bounded Context: Organisasi & Cabang
│   │   └── Models/
│   │       └── Branch.php
│   │
│   ├── Catalog/                   <-- Bounded Context: Master Data Barang & Harga
│   │   ├── Models/
│   │   │   ├── ProductItem.php
│   │   │   ├── BranchProduct.php
│   │   │   └── ProductPricing.php
│   │   ├── Actions/               <-- Business Logic Unit (Use Cases)
│   │   │   ├── CreateNewProductAction.php
│   │   │   └── UpdateBranchStockThresholdAction.php
│   │
│   └── Inventory/                 <-- Bounded Context: Manajemen Stok Fisik Gudang
│       └── Models/
│           └── Inventory.php
```

---

## 2. Implementasi Database Melalui Eloquent Model

### A. Domain Catalog: `ProductItem.php` (The Hub)
```php
namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Inventory\Models\Inventory;

class ProductItem extends Model
{
    use HasUuids;

    protected $table = 'product_items';
    protected $fillable = ['sku', 'barcode', 'name', 'category_id', 'uom_default', 'is_active'];

    public function branchConfigurations(): HasMany
    {
        return $this->hasMany(BranchProduct::class, 'product_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_id');
    }
}
```

### B. Domain Inventory: `Inventory.php` (Stok Fisik)
```php
namespace App\Domains\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Catalog\Models\ProductItem;

class Inventory extends Model
{
    use HasUuids;

    protected $table = 'inventories';
    public $timestamps = false;
    protected $fillable = ['branch_id', 'product_id', 'qty_on_hand', 'qty_reserved'];

    protected $casts = [
        'qty_on_hand' => 'float',
        'qty_reserved' => 'float',
        'qty_available' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductItem::class, 'product_id');
    }
}
```

---

## 3. Penerapan Kriteria Bisnis Lewat Single Action Class (Use Case)

### Contoh: `CreateNewProductAction.php`
```php
namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ProductItem;
use App\Domains\Organization\Models\Branch;
use App\Domains\Inventory\Models\Inventory;
use Illuminate\Support\Facades\DB;

class CreateNewProductAction
{
    public function execute(array $data): ProductItem
    {
        return DB::transaction(function () use ($data) {
            $product = ProductItem::create([
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'name' => $data['name'],
                'category_id' => $data['category_id'] ?? null,
                'uom_default' => $data['uom_default'],
            ]);

            $activeBranches = Branch::where('is_active', true)->get();

            foreach ($activeBranches as $branch) {
                Inventory::create([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'qty_on_hand' => 0.0000,
                    'qty_reserved' => 0.0000,
                ]);
            }

            return $product;
        });
    }
}
```