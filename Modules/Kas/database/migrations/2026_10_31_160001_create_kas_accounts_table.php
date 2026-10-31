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
        Schema::create('kas_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('type', 30)->default('kasir_toko');
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_holder', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_accounts');
    }
};
