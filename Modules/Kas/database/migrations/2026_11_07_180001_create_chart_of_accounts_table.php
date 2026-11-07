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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('account_code', 50)->unique();
            $table->string('account_name', 150);
            $table->string('account_category', 30); // asset, liability, equity, revenue, cogs, expense
            $table->string('normal_balance', 10)->default('debit'); // debit, credit
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->unsignedTinyInteger('level')->default(1); // 1 = Header, 2 = Sub Header, 3 = Detail / Postable
            $table->boolean('is_header')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['account_category', 'is_active']);
            $table->index(['branch_id', 'level']);
        });

        Schema::table('kas_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('kas_accounts', 'chart_of_account_id')) {
                $table->foreignId('chart_of_account_id')->nullable()->after('type')->constrained('chart_of_accounts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('kas_accounts', 'chart_of_account_id')) {
                $table->dropForeign(['chart_of_account_id']);
                $table->dropColumn('chart_of_account_id');
            }
        });

        Schema::dropIfExists('chart_of_accounts');
    }
};
