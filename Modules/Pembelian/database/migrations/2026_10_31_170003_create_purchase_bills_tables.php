<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 50)->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('status', 30)->default('unpaid');
            $table->date('bill_date')->index();
            $table->date('due_date')->index();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('remaining_balance', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_bill_id')->constrained('purchase_bills')->cascadeOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()->constrained('goods_receipt_items')->nullOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_bill_items');
        Schema::dropIfExists('purchase_bills');
    }
};
