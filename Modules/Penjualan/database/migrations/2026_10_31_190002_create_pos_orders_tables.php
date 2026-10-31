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
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_shift_id')->nullable()->constrained('pos_shifts')->nullOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('order_number', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('payment_method', 30)->default('cash');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2);
            $table->decimal('cash_paid', 15, 2)->default(0.00);
            $table->decimal('change_given', 15, 2)->default(0.00);
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kas_transaction_id')->nullable()->constrained('kas_transactions')->nullOnDelete();
            $table->jsonb('snapshot_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
    }
};
