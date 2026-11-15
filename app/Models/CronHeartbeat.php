<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model pencatat detak eksekusi terakhir (heartbeat) jadwal cron Laravel Scheduler.
 */
class CronHeartbeat extends Model
{
    protected $fillable = [
        'command_name',
        'schedule_expression',
        'status',
        'duration_ms',
        'output_summary',
        'last_started_at',
        'last_finished_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'last_started_at' => 'datetime',
            'last_finished_at' => 'datetime',
        ];
    }
}
