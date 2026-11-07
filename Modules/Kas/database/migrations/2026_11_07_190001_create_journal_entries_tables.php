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
        if (! Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table): void {
                $table->id();
                $table->string('entry_number', 50)->unique();
                $table->date('entry_date');
                $table->string('reference_type', 100)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('creator_employee_id')->nullable()->constrained('employees')->nullOnDelete();
                $table->string('description', 255);
                $table->string('status', 20)->default('posted'); // posted, draft, void
                $table->decimal('total_debit', 15, 2)->default(0);
                $table->decimal('total_credit', 15, 2)->default(0);
                $table->timestamps();

                $table->index(['entry_date', 'status']);
                $table->index(['reference_type', 'reference_id']);
            });
        }

        if (! Schema::hasTable('journal_entry_lines')) {
            Schema::create('journal_entry_lines', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
                $table->foreignId('chart_of_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('memo', 255)->nullable();
                $table->timestamps();

                $table->index(['journal_entry_id', 'chart_of_account_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
