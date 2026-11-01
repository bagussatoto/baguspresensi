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
            if (! Schema::hasColumn('shift_sales_summaries', 'handover_amount')) {
                $table->decimal('handover_amount', 15, 2)->nullable()->after('handover_notes');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'handover_recipient')) {
                $table->string('handover_recipient', 120)->nullable()->after('handover_amount');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'handover_status')) {
                $table->string('handover_status', 40)->default('completed')->after('handover_recipient');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'handover_at')) {
                $table->timestamp('handover_at')->nullable()->after('handover_status');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'handover_by')) {
                $table->string('handover_by', 120)->nullable()->after('handover_at');
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
                'handover_amount',
                'handover_recipient',
                'handover_status',
                'handover_at',
                'handover_by',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('shift_sales_summaries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
