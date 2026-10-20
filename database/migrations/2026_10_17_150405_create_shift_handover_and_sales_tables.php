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
        // 1. Checklist Stok Barang, Kertas, Minuman/Makanan Bebas, Hardware & Billing Saat Serah Terima Shift
        Schema::create('shift_handover_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->date('check_date');

            // 1. Kertas
            $table->boolean('paper_a4')->default(true);
            $table->integer('paper_a4_qty')->default(500); // lembar / rim
            $table->boolean('paper_f4')->default(true);
            $table->integer('paper_f4_qty')->default(500); // lembar / rim

            // 2. Minuman (Checkbox Minuman Bebas)
            $table->boolean('drink_free_checked')->default(true);
            $table->json('drink_items')->nullable(); // detail minuman yang dicek / bebas
            $table->text('drink_notes')->nullable();

            // 3. Makanan (Checkbox Makanan Bebas)
            $table->boolean('food_free_checked')->default(true);
            $table->json('food_items')->nullable(); // detail makanan yang dicek / bebas
            $table->text('food_notes')->nullable();

            // 4. Cek Hardware
            $table->string('hardware_status', 30)->default('normal'); // normal, warning, issue
            $table->json('hardware_checklist')->nullable(); // pc stations, monitor, keyboard, printer, lan, cctv
            $table->text('hardware_notes')->nullable();

            // 5. Cek Daftar Billing (Pendapatan setiap shift)
            $table->decimal('billing_shift_income', 15, 2)->default(0.00);
            $table->boolean('billing_verified')->default(true);
            $table->json('billing_details')->nullable(); // rincian omset shift 1, 2, 3, 4

            // Status checklist
            $table->string('status', 30)->default('completed'); // draft, completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 2. Permintaan Stok Barang (Restock Request) dari Halaman Penjualan
        Schema::create('shift_stock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->string('item_category', 50); // kertas, minuman, makanan, hardware, atk
            $table->string('item_name', 200);
            $table->integer('qty_requested')->default(1);
            $table->string('unit', 50)->default('pcs'); // pcs, rim, pack, botol
            $table->string('urgency', 20)->default('normal'); // normal, urgent
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved, in_transit, fulfilled, rejected
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
        });

        // 3. Rekapitulasi Penjualan Shift (Billing Internet + Penjualan Makanan Minuman)
        Schema::create('shift_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->date('work_date');
            $table->decimal('total_fnb_sales', 15, 2)->default(0.00);
            $table->decimal('total_internet_sales', 15, 2)->default(0.00);
            $table->decimal('total_revenue', 15, 2)->default(0.00);
            $table->decimal('cash_in_drawer', 15, 2)->default(0.00);
            $table->decimal('non_cash_amount', 15, 2)->default(0.00);
            $table->text('handover_notes')->nullable();
            $table->string('status', 30)->default('open'); // open, closed
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_sales_summaries');
        Schema::dropIfExists('shift_stock_requests');
        Schema::dropIfExists('shift_handover_checklists');
    }
};
