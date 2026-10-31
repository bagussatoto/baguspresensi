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
        Schema::create('kas_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->foreignId('from_kas_account_id')->constrained('kas_accounts')->restrictOnDelete();
            $table->foreignId('to_kas_account_id')->constrained('kas_accounts')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('admin_fee', 15, 2)->default(0.00);
            $table->date('transfer_date')->index();
            $table->string('reference_number', 100)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_transfers');
    }
};
