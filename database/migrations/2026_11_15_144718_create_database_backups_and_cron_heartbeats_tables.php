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
        Schema::create('database_backups', function (Blueprint $table): void {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('disk', 32)->default('local');
            $table->string('driver', 32)->default('pgsql');
            $table->string('database_name')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('trigger_type', 20)->default('cli');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cron_heartbeats', function (Blueprint $table): void {
            $table->id();
            $table->string('command_name')->unique();
            $table->string('schedule_expression', 64)->nullable();
            $table->string('status', 20)->default('idle');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('output_summary')->nullable();
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('last_finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_heartbeats');
        Schema::dropIfExists('database_backups');
    }
};
