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
        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->integer('raw_print_pages')->nullable()->after('locked_print_pages');
            $table->decimal('raw_print_amount', 12, 2)->nullable()->after('raw_print_pages');
            $table->integer('print_adjustment_pages')->default(0)->after('raw_print_amount');
            $table->decimal('print_adjustment_amount', 12, 2)->default(0)->after('print_adjustment_pages');
            $table->string('print_adjustment_reason', 100)->nullable()->after('print_adjustment_amount');
            $table->text('print_adjustment_notes')->nullable()->after('print_adjustment_reason');
            $table->string('print_adjusted_by', 120)->nullable()->after('print_adjustment_notes');
            $table->string('print_adjustment_status', 50)->default('none')->after('print_adjusted_by');
            $table->string('print_adjustment_approved_by', 120)->nullable()->after('print_adjustment_status');
            $table->timestamp('print_adjustment_approved_at')->nullable()->after('print_adjustment_approved_by');
            $table->string('print_adjustment_rejected_by', 120)->nullable()->after('print_adjustment_approved_at');
            $table->timestamp('print_adjustment_rejected_at')->nullable()->after('print_adjustment_rejected_by');
            $table->text('print_adjustment_rejection_reason')->nullable()->after('print_adjustment_rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'raw_print_pages',
                'raw_print_amount',
                'print_adjustment_pages',
                'print_adjustment_amount',
                'print_adjustment_reason',
                'print_adjustment_notes',
                'print_adjusted_by',
                'print_adjustment_status',
                'print_adjustment_approved_by',
                'print_adjustment_approved_at',
                'print_adjustment_rejected_by',
                'print_adjustment_rejected_at',
                'print_adjustment_rejection_reason',
            ]);
        });
    }
};
