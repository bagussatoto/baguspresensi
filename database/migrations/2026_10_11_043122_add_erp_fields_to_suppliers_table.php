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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('address');
            $table->string('city_note', 100)->nullable()->after('city');
            $table->string('contact_person', 100)->nullable()->after('city_note');
            $table->string('npwp', 50)->nullable()->after('tax_id_number');
            $table->string('nik', 50)->nullable()->after('npwp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['city', 'city_note', 'contact_person', 'npwp', 'nik']);
        });
    }
};
