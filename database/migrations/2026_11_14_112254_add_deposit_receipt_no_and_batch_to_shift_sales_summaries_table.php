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
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_receipt_no')) {
                $table->string('deposit_receipt_no', 80)->nullable()->after('deposit_status');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_batch_id')) {
                $table->string('deposit_batch_id', 80)->nullable()->after('deposit_receipt_no');
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
            if (Schema::hasColumn('shift_sales_summaries', 'deposit_receipt_no')) {
                $table->dropColumn('deposit_receipt_no');
            }
            if (Schema::hasColumn('shift_sales_summaries', 'deposit_batch_id')) {
                $table->dropColumn('deposit_batch_id');
            }
        });
    }
};
