<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendanceSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service: Pengelolaan Pengaturan Dinamis Presensi dengan Caching Memory.
 */
class AttendanceSettingService
{
    protected const CACHE_PREFIX = 'attendance_setting_';

    /**
     * Dapatkan nilai pengaturan berdasarkan key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(self::CACHE_PREFIX.$key, function () use ($key, $default) {
            /** @var AttendanceSetting|null $setting */
            $setting = AttendanceSetting::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return $setting->getTypedValue() ?? $default;
        });
    }

    /**
     * Set atau mutakhirkan nilai pengaturan dan lakukan invalidasi cache.
     */
    public function set(string $key, mixed $value): AttendanceSetting
    {
        $setting = AttendanceSetting::where('key', $key)->first();

        $stringValue = is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        if ($setting) {
            $setting->update(['value' => $stringValue]);
        } else {
            $setting = AttendanceSetting::create([
                'key' => $key,
                'value' => $stringValue,
                'type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_array($value) ? 'json' : 'string')),
                'group' => 'general',
                'label' => ucwords(str_replace(['.', '_'], ' ', $key)),
            ]);
        }

        Cache::forget(self::CACHE_PREFIX.$key);

        return $setting;
    }

    /**
     * Dapatkan seluruh pengaturan dikelompokkan berdasarkan grup.
     *
     * @return Collection<string, Collection<int, AttendanceSetting>>
     */
    public function getAllGrouped(): Collection
    {
        return AttendanceSetting::orderBy('group')->orderBy('id')->get()->groupBy('group');
    }

    /**
     * Bersihkan seluruh cache pengaturan.
     */
    public function clearCache(): void
    {
        $keys = AttendanceSetting::pluck('key');
        foreach ($keys as $key) {
            Cache::forget(self::CACHE_PREFIX.$key);
        }
    }
}
