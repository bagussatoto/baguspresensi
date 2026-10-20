<?php

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
        Schema::create('employee_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 200);
            $table->string('phone', 50)->nullable();
            $table->string('username', 100)->index();
            $table->string('password', 255);
            $table->string('pin', 255);
            $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_employee_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_registrations');
    }
};
