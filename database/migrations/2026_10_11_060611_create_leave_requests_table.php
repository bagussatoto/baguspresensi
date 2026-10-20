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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('leave_type', 30)->index();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_days')->default(1);
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
