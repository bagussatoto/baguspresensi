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
            $table->string('billing_adjustment_status', 50)->default('none')->after('billing_adjusted_by');
            $table->string('billing_adjustment_approved_by', 120)->nullable()->after('billing_adjustment_status');
            $table->timestamp('billing_adjustment_approved_at')->nullable()->after('billing_adjustment_approved_by');
            $table->string('billing_adjustment_rejected_by', 120)->nullable()->after('billing_adjustment_approved_at');
            $table->timestamp('billing_adjustment_rejected_at')->nullable()->after('billing_adjustment_rejected_by');
            $table->text('billing_adjustment_rejection_reason')->nullable()->after('billing_adjustment_rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'billing_adjustment_status',
                'billing_adjustment_approved_by',
                'billing_adjustment_approved_at',
                'billing_adjustment_rejected_by',
                'billing_adjustment_rejected_at',
                'billing_adjustment_rejection_reason',
            ]);
        });
    }
};
