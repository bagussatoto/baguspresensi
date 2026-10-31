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
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_status')) {
                $table->string('deposit_status', 40)->default('unsubmitted')->after('status');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'deposited_amount')) {
                $table->decimal('deposited_amount', 15, 2)->nullable()->after('deposit_status');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'retained_drawer_cash')) {
                $table->decimal('retained_drawer_cash', 15, 2)->default(0)->after('deposited_amount');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_method')) {
                $table->string('deposit_method', 60)->nullable()->after('retained_drawer_cash');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_recipient')) {
                $table->string('deposit_recipient', 120)->nullable()->after('deposit_method');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_notes')) {
                $table->text('deposit_notes')->nullable()->after('deposit_recipient');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'deposited_at')) {
                $table->timestamp('deposited_at')->nullable()->after('deposit_notes');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_verified_at')) {
                $table->timestamp('deposit_verified_at')->nullable()->after('deposited_at');
            }
            if (! Schema::hasColumn('shift_sales_summaries', 'deposit_verified_by')) {
                $table->string('deposit_verified_by', 120)->nullable()->after('deposit_verified_at');
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
                'deposit_status',
                'deposited_amount',
                'retained_drawer_cash',
                'deposit_method',
                'deposit_recipient',
                'deposit_notes',
                'deposited_at',
                'deposit_verified_at',
                'deposit_verified_by',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('shift_sales_summaries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
