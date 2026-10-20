<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('customer_type', 30)->default('retail');
            $table->foreignId('pricelist_id')->nullable()->constrained('pricelists')->nullOnDelete();
            $table->string('tax_id_type', 30)->default('non_wp');
            $table->string('tax_id_number', 30)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->decimal('current_credit_used', 15, 2)->default(0.00);
            $table->unsignedInteger('payment_term_days')->default(0);
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('tax_id_type', 30)->default('non_wp');
            $table->string('tax_id_number', 30)->nullable();
            $table->unsignedInteger('payment_term_days')->default(0);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};
