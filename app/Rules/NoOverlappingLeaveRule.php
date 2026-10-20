<?php

declare(strict_types=1);

namespace App\Rules;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Leave\Models\LeaveRequest;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Rule: Mencegah tumpang tindih (overlapping) tanggal permohonan cuti / izin karyawan.
 */
class NoOverlappingLeaveRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  int|null  $employeeId  ID Karyawan opsional jika tidak ada di payload request
     */
    public function __construct(protected ?int $employeeId = null) {}

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $employeeId = $this->employeeId ?? ($this->data['employee_id'] ?? null);
        $startDate = $this->data['start_date'] ?? null;
        $endDate = $value; // attribute is usually end_date or validated on start_date

        if (! $employeeId || ! $startDate || ! $endDate) {
            return;
        }

        $overlapExists = LeaveRequest::where('employee_id', (int) $employeeId)
            ->whereIn('status', [ApprovalStatus::Pending, ApprovalStatus::Approved])
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->exists();

        if ($overlapExists) {
            $fail('Rentang tanggal permohonan cuti bertabrakan (overlapping) dengan permohonan izin/cuti yang sudah ada.');
        }
    }
}
