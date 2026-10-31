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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('payment_status', 30)->default('unpaid'); // unpaid, partial, paid
            $table->date('invoice_date')->index();
            $table->date('due_date')->index();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('remaining_balance', 15, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('snapshot_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
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
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
    }
};
