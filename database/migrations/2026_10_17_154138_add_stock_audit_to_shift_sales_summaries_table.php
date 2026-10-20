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
        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->json('stock_audit')->nullable()->after('handover_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->dropColumn('stock_audit');
        });
    }
};
