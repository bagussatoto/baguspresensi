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
            if (! Schema::hasColumn('shift_sales_summaries', 'discrepancy_resolution_status')) {
                $table->string('discrepancy_resolution_status', 40)->default('unresolved')->after('cash_difference');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'discrepancy_resolution_action')) {
                $table->string('discrepancy_resolution_action', 60)->nullable()->after('discrepancy_resolution_status');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'discrepancy_resolution_notes')) {
                $table->text('discrepancy_resolution_notes')->nullable()->after('discrepancy_resolution_action');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'discrepancy_resolved_by')) {
                $table->string('discrepancy_resolved_by', 120)->nullable()->after('discrepancy_resolution_notes');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'discrepancy_resolved_at')) {
                $table->timestamp('discrepancy_resolved_at')->nullable()->after('discrepancy_resolved_by');
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
            $cols = [
                'discrepancy_resolution_status',
                'discrepancy_resolution_action',
                'discrepancy_resolution_notes',
                'discrepancy_resolved_by',
                'discrepancy_resolved_at',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('shift_sales_summaries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
