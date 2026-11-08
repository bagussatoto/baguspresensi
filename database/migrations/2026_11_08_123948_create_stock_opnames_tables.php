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
        Schema::create('stock_opnames', function (Blueprint $table): void {
            $table->id();
            $table->string('opname_number', 50)->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('opname_date');
            $table->foreignId('conducted_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status', 30)->default('draft'); // draft, completed, adjusted, cancelled
            $table->integer('total_items_counted')->default(0);
            $table->integer('total_discrepancy_items')->default(0);
            $table->decimal('total_variance_cost', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['warehouse_id', 'opname_date']);
        });

        Schema::create('stock_opname_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('bin_location', 50)->nullable();
            $table->integer('qty_system')->default(0);
            $table->integer('qty_physical')->default(0);
            $table->integer('qty_variance')->default(0); // qty_physical - qty_system
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_variance_cost', 15, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['stock_opname_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
