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
        Schema::create('system_menus', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('presensi2')->index()->comment('Grup modul menu (misal: presensi2, admin, kasir)');
            $table->string('code', 50)->unique()->comment('Kode unik menu');
            $table->string('name', 100)->comment('Nama label menu');
            $table->string('url')->comment('URL lengkap sistem');
            $table->string('route_name', 100)->nullable()->comment('Nama route Laravel');
            $table->string('icon', 50)->nullable()->comment('Icon emoji atau class');
            $table->integer('sort_order')->default(0)->comment('Urutan tampilan menu');
            $table->boolean('is_active')->default(true)->comment('Status aktif menu');
            $table->string('target', 20)->default('_self')->comment('Target link (_self, _blank)');
            $table->text('description')->nullable()->comment('Deskripsi fungsi menu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_menus');
    }
};
