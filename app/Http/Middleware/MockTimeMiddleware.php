<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MockTimeMiddleware
{
    /**
     * Middleware untuk simulasi waktu (Time-Travel / Mock Jam) pada Portal Presensi.
     * Hanya aktif saat debug mode atau environment local/testing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! (config('app.debug') || app()->environment('local', 'testing'))) {
            return $next($request);
        }

        // 1. Cek apakah ada instruksi set / reset mock time di request
        $mockInput = $request->input('mock_time') ?? $request->query('mock_time');
        if ($mockInput !== null) {
            $val = strtolower(trim((string) $mockInput));
            if (in_array($val, ['reset', 'normal', 'clear', 'off', '0', ''], true)) {
                $request->session()->forget('presensi2_mock_time');
                Carbon::setTestNow();
            } else {
                $resolved = $this->resolveMockDateTime($val, (string) ($request->input('mock_date') ?? $request->query('mock_date')));
                if ($resolved !== null) {
                    $request->session()->put('presensi2_mock_time', $resolved);
                }
            }
        }

        // 2. Terapkan mock time dari session jika tersimpan
        $isMockApplied = false;
        if ($request->session()->has('presensi2_mock_time')) {
            $stored = (string) $request->session()->get('presensi2_mock_time');
            try {
                $mockCarbon = Carbon::parse($stored);
                Carbon::setTestNow($mockCarbon);
                view()->share('activeMockTime', $mockCarbon);
                $isMockApplied = true;
            } catch (\Throwable) {
                $request->session()->forget('presensi2_mock_time');
                Carbon::setTestNow();
            }
        }

        try {
            return $next($request);
        } finally {
            // PENTING: Kembalikan Carbon ke waktu real SEBELUM session disimpan & cookie dikirim ke browser!
            // Jika tidak di-reset di finally, DatabaseSessionHandler akan menulis last_activity di masa lampau (misal: 01:05),
            // dan session cookie akan kadaluarsa di browser, menyebabkan session terhapus dan error 419 CSRF Token Mismatch.
            if ($isMockApplied) {
                Carbon::setTestNow();
            }
        }
    }

    /**
     * Resolusi alias shift atau string jam ke format DateTime standar (Y-m-d H:i:s).
     */
    protected function resolveMockDateTime(string $input, ?string $customDate = null): ?string
    {
        $baseDate = ! empty($customDate) ? $customDate : Carbon::today()->format('Y-m-d');

        // Shortcut preset shift
        $shortcuts = [
            'shift1' => '01:05:00',
            'shift-1' => '01:05:00',
            's1' => '01:05:00',
            'shift2' => '07:05:00',
            'shift-2' => '07:05:00',
            's2' => '07:05:00',
            'shift3' => '13:05:00',
            'shift-3' => '13:05:00',
            's3' => '13:05:00',
            'shift4' => '19:05:00',
            'shift-4' => '19:05:00',
            's4' => '19:05:00',
            'bo' => '08:35:00',
            'shiftbo' => '08:35:00',
        ];

        if (isset($shortcuts[$input])) {
            return "{$baseDate} {$shortcuts[$input]}";
        }

        // Format H:i atau H:i:s
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $input, $m)) {
            $h = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $i = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $s = isset($m[3]) ? str_pad($m[3], 2, '0', STR_PAD_LEFT) : '00';

            return "{$baseDate} {$h}:{$i}:{$s}";
        }

        // Full DateTime parse
        try {
            return Carbon::parse($input)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
