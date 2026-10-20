<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricelists', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('customer_type', 30)->nullable()->index(); // retail, grosir, etc.
            $table->string('currency', 10)->default('IDR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pricelist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricelist_id')->constrained('pricelists')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('min_qty')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['pricelist_id', 'product_variant_id', 'min_qty'], 'pricelist_variant_min_qty_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricelist_items');
        Schema::dropIfExists('pricelists');
    }
};
