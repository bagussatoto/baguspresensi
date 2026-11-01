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
            $table->decimal('raw_billing_amount', 12, 2)->nullable()->after('locked_billing_amount');
            $table->decimal('billing_adjustment_amount', 12, 2)->default(0)->after('raw_billing_amount');
            $table->string('billing_adjustment_reason', 100)->nullable()->after('billing_adjustment_amount');
            $table->text('billing_adjustment_notes')->nullable()->after('billing_adjustment_reason');
            $table->integer('billing_blackout_minutes')->nullable()->after('billing_adjustment_notes');
            $table->integer('billing_blackout_pcs')->nullable()->after('billing_blackout_minutes');
            $table->string('billing_adjusted_by', 120)->nullable()->after('billing_blackout_pcs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'raw_billing_amount',
                'billing_adjustment_amount',
                'billing_adjustment_reason',
                'billing_adjustment_notes',
                'billing_blackout_minutes',
                'billing_blackout_pcs',
                'billing_adjusted_by',
            ]);
        });
    }
};
