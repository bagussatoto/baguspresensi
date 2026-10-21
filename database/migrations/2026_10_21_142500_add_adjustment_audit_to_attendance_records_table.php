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
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->boolean('is_adjusted')->default(false)->after('status');
            $table->unsignedBigInteger('adjusted_by')->nullable()->after('is_adjusted');
            $table->timestamp('adjusted_at')->nullable()->after('adjusted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->dropColumn(['is_adjusted', 'adjusted_by', 'adjusted_at']);
        });
    }
};
