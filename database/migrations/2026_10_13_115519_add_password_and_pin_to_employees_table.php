<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom password (Bcrypt) dan PIN 6-digit (Bcrypt) pada tabel employees
     * untuk verifikasi kredensial portal presensi karyawan v2.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->string('pin', 255)->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['password', 'pin']);
        });
    }
};
