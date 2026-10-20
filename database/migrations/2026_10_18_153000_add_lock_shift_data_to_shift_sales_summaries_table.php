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
            $table->boolean('is_billing_locked')->default(false)->after('total_internet_sales');
            $table->decimal('locked_billing_amount', 12, 2)->nullable()->after('is_billing_locked');
            $table->timestamp('locked_billing_at')->nullable()->after('locked_billing_amount');

            $table->boolean('is_print_locked')->default(false)->after('locked_billing_at');
            $table->decimal('locked_print_amount', 12, 2)->nullable()->after('is_print_locked');
            $table->integer('locked_print_pages')->nullable()->after('locked_print_amount');
            $table->timestamp('locked_print_at')->nullable()->after('locked_print_pages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'is_billing_locked',
                'locked_billing_amount',
                'locked_billing_at',
                'is_print_locked',
                'locked_print_amount',
                'locked_print_pages',
                'locked_print_at',
            ]);
        });
    }
};
