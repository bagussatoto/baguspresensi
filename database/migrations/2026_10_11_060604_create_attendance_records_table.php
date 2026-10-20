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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('work_date')->index();

            // Clock In
            $table->dateTimeTz('clock_in_at')->nullable();
            $table->decimal('clock_in_lat', 10, 8)->nullable();
            $table->decimal('clock_in_lng', 11, 8)->nullable();
            $table->decimal('clock_in_distance_meters', 8, 2)->nullable();
            $table->string('clock_in_photo_path')->nullable();

            // Clock Out
            $table->dateTimeTz('clock_out_at')->nullable();
            $table->decimal('clock_out_lat', 10, 8)->nullable();
            $table->decimal('clock_out_lng', 11, 8)->nullable();
            $table->decimal('clock_out_distance_meters', 8, 2)->nullable();
            $table->string('clock_out_photo_path')->nullable();

            // Status & Calculations
            $table->string('status', 30)->default('present')->index();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->unsignedInteger('duration_minutes')->default(0);

            // Anti-Fraud & Auditing
            $table->string('verification_method', 30)->default('gps_selfie');
            $table->json('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_mock_location_detected')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'work_date', 'shift_id'], 'uq_attendance_emp_date_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
