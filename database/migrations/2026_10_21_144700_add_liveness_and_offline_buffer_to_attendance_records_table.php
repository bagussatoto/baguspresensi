<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Stage 2 Liveness Detection & Resilient Offline Buffer.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            // Poin 2.1 Liveness Detection Score
            $table->decimal('liveness_score', 4, 2)->nullable()->after('is_mock_location_detected');

            // Poin 2.2 PWA Offline Buffer & Anti-Replay Nonce
            $table->boolean('is_offline_sync')->default(false)->after('liveness_score');
            $table->string('offline_nonce', 64)->nullable()->unique()->after('is_offline_sync');
            $table->timestamp('sync_recorded_at')->nullable()->after('offline_nonce');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->dropColumn([
                'liveness_score',
                'is_offline_sync',
                'offline_nonce',
                'sync_recorded_at',
            ]);
        });
    }
};
