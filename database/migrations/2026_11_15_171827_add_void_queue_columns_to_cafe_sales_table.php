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
        Schema::table('cafe_sales', function (Blueprint $table) {
            $table->string('void_status', 30)->nullable()->index()->after('void_at');
            $table->string('void_requested_by', 150)->nullable()->after('void_status');
            $table->timestamp('void_requested_at')->nullable()->after('void_requested_by');
            $table->string('void_reviewed_by', 150)->nullable()->after('void_requested_at');
            $table->timestamp('void_reviewed_at')->nullable()->after('void_reviewed_by');
            $table->string('void_rejected_reason', 500)->nullable()->after('void_reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cafe_sales', function (Blueprint $table) {
            $table->dropColumn([
                'void_status',
                'void_requested_by',
                'void_requested_at',
                'void_reviewed_by',
                'void_reviewed_at',
                'void_rejected_reason',
            ]);
        });
    }
};
