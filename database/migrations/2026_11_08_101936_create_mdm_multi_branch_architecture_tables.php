<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengimplementasikan arsitektur Master Data Management (MDM) ERP Multi-Cabang Hub-and-Spoke.
     */
    public function up(): void
    {
        // 1. Tambah tipe entitas cabang (HQ, DC, STORE) pada tabel branches
        if (Schema::hasTable('branches') && ! Schema::hasColumn('branches', 'type')) {
            Schema::table('branches', function (Blueprint $table): void {
                $table->string('type', 20)->default('STORE')->after('name');
            });

            // Set cabang yang is_headquarters = true menjadi type HQ
            DB::table('branches')->where('is_headquarters', true)->update(['type' => 'HQ']);
        }

        // 2. Buat tabel branch_products (The Spoke: Lokalisasi Produk per Cabang)
        if (! Schema::hasTable('branch_products')) {
            Schema::create('branch_products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->string('bin_location', 50)->nullable()->comment('Lokasi rak/lorong spesifik cabang');
                $table->decimal('min_stock', 12, 2)->default(0)->comment('Batas stok minimum cabang');
                $table->decimal('max_stock', 12, 2)->default(0)->comment('Batas kapasitas simpan cabang');
                $table->decimal('reorder_point', 12, 2)->default(0)->comment('Batas ROP pemicu permintaan transfer dari DC');
                $table->boolean('is_locally_active')->default(true)->comment('Status ketersediaan di cabang bersangkutan');
                $table->timestamps();

                $table->unique(['branch_id', 'product_variant_id'], 'uq_branch_product_variant');
                $table->index(['branch_id', 'is_locally_active']);
            });
        }

        // 3. Buat tabel product_pricings (Zonasi Penetapan Harga Multi-Cabang)
        if (! Schema::hasTable('product_pricings')) {
            Schema::create('product_pricings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->comment('Cabang spesifik atau null jika berlaku untuk seluruh cabang dalam zona');
                $table->string('zone_code', 30)->default('DEFAULT')->comment('Kode zona wilayah geografis (misal: ZONE-PUSAT, ZONE-JABODETABEK)');
                $table->decimal('base_cost', 15, 2)->default(0)->comment('HPP dasar zona');
                $table->decimal('sale_price', 15, 2)->default(0)->comment('Harga jual di zona/cabang bersangkutan');
                $table->date('start_date')->default(now()->toDateString());
                $table->date('end_date')->nullable();
                $table->timestamps();

                $table->index(['product_variant_id', 'zone_code', 'start_date']);
                $table->index(['branch_id', 'product_variant_id']);
            });
        }

        // 4. Tambah kolom qty_reserved pada stock_inventories untuk tracking stok komitmen/pesanan
        if (Schema::hasTable('stock_inventories') && ! Schema::hasColumn('stock_inventories', 'qty_reserved')) {
            Schema::table('stock_inventories', function (Blueprint $table): void {
                $table->integer('qty_reserved')->default(0)->after('qty_on_hand')->comment('Stok yang telah terikat order pelanggan / mutasi in-transit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_inventories') && Schema::hasColumn('stock_inventories', 'qty_reserved')) {
            Schema::table('stock_inventories', function (Blueprint $table): void {
                $table->dropColumn('qty_reserved');
            });
        }

        Schema::dropIfExists('product_pricings');
        Schema::dropIfExists('branch_products');

        if (Schema::hasTable('branches') && Schema::hasColumn('branches', 'type')) {
            Schema::table('branches', function (Blueprint $table): void {
                $table->dropColumn('type');
            });
        }
    }
};
