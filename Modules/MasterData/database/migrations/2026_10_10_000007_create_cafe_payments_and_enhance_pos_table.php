<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah field nominal bayar & kembalian ke cafe_sales
        Schema::table('cafe_sales', function (Blueprint $table) {
            $table->decimal('amount_paid', 15, 2)->default(0.00)->after('net_amount');
            $table->decimal('change_amount', 15, 2)->default(0.00)->after('amount_paid');
            $table->string('payment_reference', 100)->nullable()->after('change_amount');
            $table->text('cashier_notes')->nullable()->after('payment_status');
        });

        // 2. Buat tabel cafe_payments untuk pencatatan transaksi kasir
        Schema::create('cafe_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cafe_sale_id')->constrained('cafe_sales')->cascadeOnDelete();
            $table->string('payment_number', 100)->unique();
            $table->string('payment_method', 30)->default('cash'); // cash, qris, transfer, debit
            $table->decimal('amount_due', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('change_amount', 15, 2)->default(0.00);
            $table->string('payment_reference', 150)->nullable();
            $table->string('status', 30)->default('completed'); // completed, pending, refunded
            $table->timestamp('paid_at')->useCurrent();
            $table->foreignId('cashier_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafe_payments');

        Schema::table('cafe_sales', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'change_amount', 'payment_reference', 'cashier_notes']);
        });
    }
};
