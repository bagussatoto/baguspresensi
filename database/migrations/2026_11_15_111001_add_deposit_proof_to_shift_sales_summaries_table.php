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
        if (! Schema::hasTable('shift_sales_summaries')) {
            return;
        }

        Schema::table('shift_sales_summaries', function (Blueprint $table): void {
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_proof')) {
                $table->string('deposit_proof', 255)->nullable()->after('deposit_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('shift_sales_summaries')) {
            return;
        }

        Schema::table('shift_sales_summaries', function (Blueprint $table): void {
            if (Schema::hasColumn('shift_sales_summaries', 'deposit_proof')) {
                $table->dropColumn('deposit_proof');
            }
        });
    }
};
