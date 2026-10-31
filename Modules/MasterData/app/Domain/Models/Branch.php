<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use App\Domains\Attendance\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kas\Domain\Models\KasAccount;

class Branch extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'code',
        'name',
        'is_headquarters',
        'address',
        'latitude',
        'longitude',
        'radius_meters',
        'wifi_ssid',
        'wifi_bssid',
        'allowed_network_ips',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_headquarters' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'radius_meters' => 'integer',
        'allowed_network_ips' => 'array',
    ];

    /**
     * Cek apakah BSSID (MAC Address Wi-Fi) cocok dengan access point resmi cabang.
     */
    public function matchesWifiBssid(?string $bssid): bool
    {
        if (empty($bssid) || empty($this->wifi_bssid)) {
            return false;
        }

        $cleanTarget = strtolower(str_replace(['-', ':'], '', trim($bssid)));
        $cleanExpected = strtolower(str_replace(['-', ':'], '', trim($this->wifi_bssid)));

        return $cleanTarget === $cleanExpected;
    }

    /**
     * Cek apakah IP Address client terdaftar dalam whitelist jaringan cabang.
     */
    public function matchesClientIp(?string $ip): bool
    {
        if (empty($ip) || empty($this->allowed_network_ips)) {
            return false;
        }

        $allowed = is_array($this->allowed_network_ips)
            ? $this->allowed_network_ips
            : (json_decode($this->allowed_network_ips, true) ?? []);

        return in_array(trim($ip), $allowed, true);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'branch_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'branch_id');
    }

    public function kasAccounts(): HasMany
    {
        return $this->hasMany(KasAccount::class, 'branch_id');
    }
}
