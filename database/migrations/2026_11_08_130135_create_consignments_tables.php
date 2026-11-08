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
        if (! Schema::hasTable('consignments')) {
            Schema::create('consignments', function (Blueprint $table): void {
                $table->id();
                $table->string('consignment_number', 60)->unique();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->string('type', 20)->default('inward'); // inward (vendor consignment), outward (reseller consignment)
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('partner_name', 150);
                $table->date('start_date');
                $table->date('due_settlement_date')->nullable();
                $table->string('status', 30)->default('active'); // active, settled, closed
                $table->unsignedInteger('total_items_count')->default(0);
                $table->decimal('total_consigned_value', 15, 2)->default(0);
                $table->decimal('total_sold_value', 15, 2)->default(0);
                $table->decimal('total_settled_amount', 15, 2)->default(0);
                $table->decimal('remaining_settlement_due', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['branch_id', 'type', 'status']);
                $table->index(['consignment_number']);
            });
        }

        if (! Schema::hasTable('consignment_items')) {
            Schema::create('consignment_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('consignment_id')->constrained('consignments')->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->unsignedInteger('qty_consigned')->default(0);
                $table->unsignedInteger('qty_sold')->default(0);
                $table->unsignedInteger('qty_returned')->default(0);
                $table->unsignedInteger('qty_remaining')->default(0);
                $table->decimal('subtotal_consigned', 15, 2)->default(0);
                $table->decimal('subtotal_sold', 15, 2)->default(0);
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->index(['consignment_id', 'product_variant_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_items');
        Schema::dropIfExists('consignments');
    }
};
