<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Pengaturan Dinamis Presensi (Configurable Enterprise Settings).
 */
class AttendanceSetting extends Model
{
    use HasFactory;

    protected $table = 'attendance_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Dapatkan nilai pengaturan dengan tipe data yang sesuai.
     */
    public function getTypedValue(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => json_decode($this->value, true) ?? [],
            default => (string) $this->value,
        };
    }
}
