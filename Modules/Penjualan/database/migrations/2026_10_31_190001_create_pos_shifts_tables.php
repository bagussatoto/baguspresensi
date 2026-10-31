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
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('kas_account_id')->constrained('kas_accounts')->restrictOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('shift_number', 50)->unique();
            $table->string('status', 30)->default('open');
            $table->dateTime('opened_at')->index();
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_cash', 15, 2)->default(0.00);
            $table->decimal('expected_cash', 15, 2)->default(0.00);
            $table->decimal('actual_cash', 15, 2)->nullable(); // Blind Count fisik kasir
            $table->decimal('cash_difference', 15, 2)->nullable(); // Selisih kas (actual - expected)
            $table->decimal('total_sales', 15, 2)->default(0.00);
            $table->decimal('total_cash_sales', 15, 2)->default(0.00);
            $table->decimal('total_non_cash_sales', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_shifts');
    }
};
