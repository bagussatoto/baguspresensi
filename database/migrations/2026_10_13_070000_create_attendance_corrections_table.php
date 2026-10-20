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
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('work_date')->index();

            // Jenis koreksi: clock_in, clock_out, both
            $table->string('correction_type', 30)->default('both');

            // Usulan jam masuk & pulang yang dikoreksi
            $table->dateTimeTz('proposed_clock_in_at')->nullable();
            $table->dateTimeTz('proposed_clock_out_at')->nullable();

            $table->text('reason');
            $table->string('attachment_path')->nullable(); // Foto/surat bukti pendukung

            // Status Approval
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
        Schema::dropIfExists('attendance_corrections');
    }
};
