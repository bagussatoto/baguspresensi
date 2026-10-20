<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Leave\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transformasi data permohonan cuti ke representasi JSON seragam.
 *
 * @mixin LeaveRequest
 */
class LeaveResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => [
                'id' => $this->employee?->id,
                'code' => $this->employee?->code,
                'name' => $this->employee?->name,
            ],
            'leave_type' => [
                'value' => $this->leave_type->value,
                'label' => $this->leave_type->label(),
            ],
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_days' => (int) $this->total_days,
            'reason' => $this->reason,
            'attachment_url' => $this->attachment_path ? asset($this->attachment_path) : null,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'badge_class' => $this->status->badgeClass(),
            ],
            'approver' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'rejection_note' => $this->rejection_note,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
