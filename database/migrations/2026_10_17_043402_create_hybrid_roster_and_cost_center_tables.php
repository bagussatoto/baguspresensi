<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Stage 11 Hybrid Roster & Cost Center.
     */
    public function up(): void
    {
        // 1. Tambah kolom kontrak & tipe kerja default pada tabel employees
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('default_work_type', 30)->default('shift')->after('role'); // 'shift', 'flexible'
            $table->integer('default_contract_hours')->nullable()->default(8)->after('default_work_type');
            $table->string('default_cost_center', 30)->default('5-1001')->after('default_contract_hours'); // 5-1001 (COGS), 5-1002 (OPEX)
        });

        // 2. Buat tabel roster_jadwals untuk jadwal harian berbasis konteks ERP
        Schema::create('roster_jadwals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('work_date')->index();
            $table->string('work_type', 30)->default('shift'); // 'shift', 'flexible'
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->integer('contract_hours')->default(8);
            $table->string('cost_center', 30)->default('5-1001');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date', 'work_type']);
        });

        // 3. Tambah kolom alokasi biaya & tipe kerja pada tabel attendance_records
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->string('work_type', 30)->default('shift')->after('status');
            $table->integer('effective_minutes')->default(0)->after('duration_minutes');
            $table->string('cost_center', 30)->default('5-1001')->after('effective_minutes');
            $table->foreignId('roster_id')->nullable()->after('cost_center')->constrained('roster_jadwals')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->dropForeign(['roster_id']);
            $table->dropColumn(['work_type', 'effective_minutes', 'cost_center', 'roster_id']);
        });

        Schema::dropIfExists('roster_jadwals');

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn(['default_work_type', 'default_contract_hours', 'default_cost_center']);
        });
    }
};
