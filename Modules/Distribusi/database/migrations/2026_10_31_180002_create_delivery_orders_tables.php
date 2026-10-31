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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_order_number', 50)->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('reference_number', 100)->nullable();
            $table->string('status', 30)->default('dispatched'); // draft, dispatched, delivered, cancelled
            $table->date('delivery_date')->index();
            $table->string('recipient_name', 100)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_plate', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('snapshot_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
    }
};
