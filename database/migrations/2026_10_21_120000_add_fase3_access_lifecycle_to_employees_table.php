<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom siklus hidup akses pengguna (User Access Lifecycle & Instant De-provisioning)
     * sesuai standar kepatuhan ISO/IEC 27001:2022 Klausul A.8.5 (Access Rights Management).
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            if (! Schema::hasColumn('employees', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('employees', 'deactivated_by')) {
                $table->foreignId('deactivated_by')->nullable()->after('deactivated_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('employees', 'deactivation_reason')) {
                $table->text('deactivation_reason')->nullable()->after('deactivated_by');
            }
            if (! Schema::hasColumn('employees', 'last_access_review_at')) {
                $table->timestamp('last_access_review_at')->nullable()->after('deactivation_reason');
            }
            if (! Schema::hasColumn('employees', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('last_access_review_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropForeign(['deactivated_by']);
            $table->dropColumn([
                'deactivated_at',
                'deactivated_by',
                'deactivation_reason',
                'last_access_review_at',
                'last_login_at',
            ]);
        });
    }
};
