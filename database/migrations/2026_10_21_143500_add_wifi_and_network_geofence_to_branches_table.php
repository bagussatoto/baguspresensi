<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Stage 1 Multi-Factor Wi-Fi & Network Geofence.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->string('wifi_ssid', 100)->nullable()->after('radius_meters');
            $table->string('wifi_bssid', 50)->nullable()->after('wifi_ssid'); // MAC Address AP (e.g. 00:14:22:01:23:45)
            $table->json('allowed_network_ips')->nullable()->after('wifi_bssid'); // IP Publik / Subnet Router Toko
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->dropColumn(['wifi_ssid', 'wifi_bssid', 'allowed_network_ips']);
        });
    }
};
