<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Jadwal Shift Karyawan / Operator Warnet
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('shift_name', 100); // Pagi, Sore, Malam/Begadang
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status', 30)->default('scheduled'); // scheduled, completed, swapped, off
            $table->timestamps();
        });

        // 2. Presensi / Kehadiran Karyawan Warnet
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->string('status', 30)->default('present'); // present, late, sick, leave, absent
            $table->unsignedInteger('late_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. PC Stations / Billing Units
        Schema::create('pc_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('station_code', 50); // PC-01, VIP-01
            $table->string('station_type', 100); // Reguler, VIP Gaming RTX 4070
            $table->decimal('hourly_rate', 12, 2)->default(6000.00);
            $table->string('status', 30)->default('available'); // available, occupied, maintenance
            $table->string('current_user_name', 150)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->decimal('billing_amount', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 4. Inventaris Stok Fisik Barang (F&B / Voucher) di Tiap Pos/Gudang
        Schema::create('stock_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('qty_on_hand')->default(0);
            $table->integer('safety_stock')->default(5);
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_variant_id'], 'warehouse_variant_stock_unique');
        });

        // 5. Distribusi / Transfer Stok Barang Antar-Cabang & Pos Kasir
        Schema::create('stock_transfer_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code', 50)->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('qty');
            $table->string('status', 30)->default('in_transit'); // in_transit, received, cancelled
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('operator_notes')->nullable();
            $table->timestamps();
        });

        // 6. Transaksi Penjualan Warnet (Billing + F&B + Merchandise)
        Schema::create('cafe_sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('customer_name', 150)->default('Pelanggan Warnet');
            $table->foreignId('pc_station_id')->nullable()->constrained('pc_stations')->nullOnDelete();
            $table->decimal('total_billing', 15, 2)->default(0.00);
            $table->decimal('total_fnb', 15, 2)->default(0.00);
            $table->decimal('gross_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2)->default(0.00);
            $table->string('payment_method', 30)->default('cash'); // cash, qris, transfer
            $table->string('payment_status', 30)->default('paid');
            $table->timestamps();
        });

        Schema::create('cafe_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cafe_sale_id')->constrained('cafe_sales')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('item_type', 30)->default('fnb'); // billing, fnb, voucher
            $table->string('item_name', 200);
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 7. Komisi Operator / Kasir Warnet
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedInteger('total_shift_hours')->default(0);
            $table->decimal('total_fnb_sales', 15, 2)->default(0.00);
            $table->decimal('commission_fnb_rate', 5, 2)->default(5.00); // 5%
            $table->decimal('commission_fnb_amount', 15, 2)->default(0.00);
            $table->decimal('total_billing_sales', 15, 2)->default(0.00);
            $table->decimal('commission_billing_rate', 5, 2)->default(2.00); // 2%
            $table->decimal('commission_billing_amount', 15, 2)->default(0.00);
            $table->decimal('bonus_shift_amount', 15, 2)->default(0.00);
            $table->decimal('total_commission', 15, 2)->default(0.00);
            $table->string('status', 30)->default('approved'); // pending, approved, paid
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('cafe_sale_items');
        Schema::dropIfExists('cafe_sales');
        Schema::dropIfExists('stock_transfer_dispatches');
        Schema::dropIfExists('stock_inventories');
        Schema::dropIfExists('pc_stations');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employee_schedules');
    }
};
