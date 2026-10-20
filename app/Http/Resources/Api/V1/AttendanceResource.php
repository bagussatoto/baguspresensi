<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Attendance\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transformasi data presensi ke representasi JSON seragam.
 *
 * @mixin AttendanceRecord
 */
class AttendanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_date' => $this->work_date?->toDateString(),
            'employee' => [
                'id' => $this->employee?->id,
                'code' => $this->employee?->code,
                'name' => $this->employee?->name,
                'username' => $this->employee?->username,
            ],
            'branch' => [
                'id' => $this->branch?->id,
                'code' => $this->branch?->code,
                'name' => $this->branch?->name,
            ],
            'shift' => $this->shift ? [
                'id' => $this->shift->id,
                'code' => $this->shift->code,
                'name' => $this->shift->name,
                'start_time' => $this->shift->start_time,
                'end_time' => $this->shift->end_time,
                'is_overnight' => (bool) $this->shift->is_overnight,
            ] : null,
            'clock_in' => [
                'at' => $this->clock_in_at?->toIso8601String(),
                'latitude' => $this->clock_in_lat,
                'longitude' => $this->clock_in_lng,
                'distance_meters' => $this->clock_in_distance_meters,
                'photo_url' => $this->clock_in_photo_path ? asset($this->clock_in_photo_path) : null,
            ],
            'clock_out' => [
                'at' => $this->clock_out_at?->toIso8601String(),
                'latitude' => $this->clock_out_lat,
                'longitude' => $this->clock_out_lng,
                'distance_meters' => $this->clock_out_distance_meters,
                'photo_url' => $this->clock_out_photo_path ? asset($this->clock_out_photo_path) : null,
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'badge_class' => $this->status->badgeClass(),
            ],
            'late_minutes' => (int) $this->late_minutes,
            'overtime_minutes' => (int) $this->overtime_minutes,
            'early_leave_minutes' => (int) $this->early_leave_minutes,
            'duration_minutes' => (int) $this->duration_minutes,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
