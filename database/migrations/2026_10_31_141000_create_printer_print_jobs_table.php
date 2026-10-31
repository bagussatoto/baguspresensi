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
        Schema::create('printer_print_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('printer_name', 150)->default('Canon USB Printer');
            $table->string('document_name', 255)->nullable();
            $table->string('client_computer', 100)->nullable();
            $table->unsignedInteger('pages')->default(1);
            $table->string('color_mode', 20)->default('bw'); // bw, color
            $table->decimal('unit_price', 10, 2)->default(1000.00);
            $table->decimal('total_amount', 12, 2)->default(1000.00);
            $table->date('work_date')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('printed_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_print_jobs');
    }
};
