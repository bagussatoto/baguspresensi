<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Service untuk pencatatan jejak audit forensik (Audit Trail).
 */
class AuditLogService
{
    /**
     * Catat log audit ke database dengan sanitasi kredensial otomatis.
     *
     * @param  string  $action  Nama aksi (e.g. 'employee_created', 'employee_status_toggled', 'leave_approved')
     * @param  Model|null  $auditable  Model yang mengalami mutasi
     * @param  array<string, mixed>|null  $oldValues  Data sebelum mutasi
     * @param  array<string, mixed>|null  $newValues  Data sesudah mutasi
     * @param  int|null  $userId  ID Admin pelaku mutasi (opsional, auto dari auth)
     */
    public function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        $actorId = $userId ?? Auth::id();

        return AuditLog::create([
            'user_id' => $actorId,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'action' => $action,
            'old_values' => $oldValues ? $this->sanitizePayload($oldValues) : null,
            'new_values' => $newValues ? $this->sanitizePayload($newValues) : null,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'System / CLI',
            'created_at' => now(),
        ]);
    }

    /**
     * Sanitasi parameter sensitif seperti password dan PIN agar tidak tersimpan dalam bentuk plain/hash transparan.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function sanitizePayload(array $payload): array
    {
        $sensitiveKeys = ['password', 'pin', 'credential', 'token', 'remember_token'];

        foreach ($payload as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $sensitiveKeys, true)) {
                $payload[$key] = '[PROTECTED]';
            } elseif (is_array($value)) {
                $payload[$key] = $this->sanitizePayload($value);
            }
        }

        return $payload;
    }
}
