<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Employee\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shift
 */
class ShiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_overnight' => (bool) $this->is_overnight,
            'grace_period_minutes' => (int) $this->grace_period_minutes,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
