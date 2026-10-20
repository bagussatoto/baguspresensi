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
        Schema::create('shift_swap_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('target_employee_id')->constrained('employees')->cascadeOnDelete();

            $table->foreignId('requester_shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('requester_date')->index();

            $table->foreignId('target_shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('target_date')->index();

            $table->text('reason');

            // Persetujuan Tahap 1: Rekan Kerja (Peer)
            $table->text('peer_note')->nullable();
            $table->dateTime('peer_responded_at')->nullable();

            // Status Dua Arah Keseluruhan: pending_peer, peer_rejected, pending_supervisor, approved, rejected, cancelled
            $table->string('status', 30)->default('pending_peer')->index();

            // Persetujuan Tahap 2: Supervisor / Admin Cabang
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
        Schema::dropIfExists('shift_swap_requests');
    }
};
