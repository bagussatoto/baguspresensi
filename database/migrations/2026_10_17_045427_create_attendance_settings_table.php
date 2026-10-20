<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Stage 13 Dynamic Attendance Settings.
     */
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique()->index();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string'); // 'string', 'integer', 'boolean', 'json', 'float'
            $table->string('group', 50)->default('general')->index();
            $table->string('label', 200);
            $table->text('description')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
