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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('username', 100)->nullable()->after('name');
            $table->string('position', 100)->nullable()->default('Kasir')->after('role');
            $table->string('jabatan', 100)->nullable()->default('sale')->after('position');
            $table->string('content_permission', 50)->nullable()->default('all')->after('jabatan');
            $table->string('seniority', 50)->nullable()->default('none')->after('content_permission');
            $table->string('evaluation', 50)->nullable()->default('none')->after('seniority');
            $table->string('employment_status', 50)->nullable()->default('tetap')->after('evaluation');
            $table->json('access_rights')->nullable()->after('employment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'position',
                'jabatan',
                'content_permission',
                'seniority',
                'evaluation',
                'employment_status',
                'access_rights',
            ]);
        });
    }
};
