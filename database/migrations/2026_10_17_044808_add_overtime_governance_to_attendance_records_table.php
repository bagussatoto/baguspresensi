<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Stage 12 Overtime Governance & Dual-Role.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->string('overtime_status', 20)->nullable()->after('overtime_minutes'); // 'pending', 'approved', 'rejected'
            $table->integer('overtime_requested_minutes')->default(0)->after('overtime_status');
            $table->integer('overtime_approved_minutes')->default(0)->after('overtime_requested_minutes');
            $table->text('overtime_reason')->nullable()->after('overtime_approved_minutes');
            $table->timestamp('overtime_requested_at')->nullable()->after('overtime_reason');
            $table->timestamp('overtime_approved_at')->nullable()->after('overtime_requested_at');
            $table->foreignId('overtime_approved_by')->nullable()->after('overtime_approved_at')->constrained('users')->nullOnDelete();
            $table->text('overtime_admin_note')->nullable()->after('overtime_approved_by');

            // Proteksi izin pulang awal jika belum memenuhi jam kontrak
            $table->boolean('early_leave_approved')->default(false)->after('early_leave_minutes');
            $table->text('early_leave_reason')->nullable()->after('early_leave_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->dropForeign(['overtime_approved_by']);
            $table->dropColumn([
                'overtime_status',
                'overtime_requested_minutes',
                'overtime_approved_minutes',
                'overtime_reason',
                'overtime_requested_at',
                'overtime_approved_at',
                'overtime_approved_by',
                'overtime_admin_note',
                'early_leave_approved',
                'early_leave_reason',
            ]);
        });
    }
};
