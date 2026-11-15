<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi tabel matriks hak akses RBAC granular per jabatan.
     */
    public function up(): void
    {
        Schema::create('rbac_role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('role_type', 20); // admin | employee
            $table->string('role_key', 50);
            $table->string('module_key', 60);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_read')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['role_type', 'role_key', 'module_key'], 'rbac_role_module_unique');
            $table->index(['role_key', 'module_key']);
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('rbac_role_permissions');
    }
};
