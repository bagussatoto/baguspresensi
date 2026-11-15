<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Model riwayat backup database PostgreSQL/SQLite.
 */
class DatabaseBackup extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'disk',
        'driver',
        'database_name',
        'file_size_bytes',
        'checksum_sha256',
        'trigger_type',
        'status',
        'triggered_by',
        'duration_ms',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'duration_ms' => 'integer',
            'triggered_by' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * Format ukuran file backup agar mudah dibaca (B, KB, MB, GB).
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size_bytes;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 2).' '.$units[$power];
    }

    /**
     * Periksa apakah file backup fisik masih tersedia pada disk.
     */
    public function fileExists(): bool
    {
        return $this->file_path !== '' && Storage::disk($this->disk ?: 'local')->exists($this->file_path);
    }
}
