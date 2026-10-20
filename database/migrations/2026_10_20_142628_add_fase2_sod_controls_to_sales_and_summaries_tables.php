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
        // 1. Blind Cash Count & Selisih Kas di Rekapitulasi Shift
        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->decimal('system_expected_cash', 15, 2)->nullable()->after('cash_in_drawer');
            $table->decimal('blind_cash_count', 15, 2)->nullable()->after('system_expected_cash');
            $table->decimal('cash_difference', 15, 2)->default(0.00)->after('blind_cash_count');
            $table->json('cash_denominations')->nullable()->after('cash_difference');
        });

        // 2. Dual-Control Supervisor Void & Diskon Kasir POS
        Schema::table('cafe_sales', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0.00)->after('discount_amount');
            $table->foreignId('discount_authorized_by')->nullable()->after('discount_percent')->constrained('employees')->nullOnDelete();
            $table->foreignId('void_by')->nullable()->after('payment_status')->constrained('employees')->nullOnDelete();
            $table->text('void_reason')->nullable()->after('void_by');
            $table->timestamp('void_at')->nullable()->after('void_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cafe_sales', function (Blueprint $table) {
            $table->dropForeign(['discount_authorized_by']);
            $table->dropForeign(['void_by']);
            $table->dropColumn([
                'discount_percent',
                'discount_authorized_by',
                'void_by',
                'void_reason',
                'void_at',
            ]);
        });

        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'system_expected_cash',
                'blind_cash_count',
                'cash_difference',
                'cash_denominations',
            ]);
        });
    }
};
