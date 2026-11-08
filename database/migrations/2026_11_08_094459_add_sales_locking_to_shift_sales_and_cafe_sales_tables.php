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
            $table->boolean('is_sales_locked')->default(false)->after('locked_print_at');
            $table->decimal('locked_sales_amount', 15, 2)->nullable()->after('is_sales_locked');
            $table->timestamp('locked_sales_at')->nullable()->after('locked_sales_amount');
        });

        Schema::table('cafe_sales', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('payment_status');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cafe_sales', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at']);
        });

        Schema::table('shift_sales_summaries', function (Blueprint $table) {
            $table->dropColumn(['is_sales_locked', 'locked_sales_amount', 'locked_sales_at']);
        });
    }
};
