<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

class ShiftSalesSummary extends Model
{
    use HasFactory;

    protected $table = 'shift_sales_summaries';

    protected $fillable = [
        'employee_id',
        'branch_id',
        'shift_id',
        'attendance_record_id',
        'work_date',
        'total_fnb_sales',
        'total_internet_sales',
        'total_revenue',
        'cash_in_drawer',
        'system_expected_cash',
        'blind_cash_count',
        'cash_difference',
        'cash_denominations',
        'non_cash_amount',
        'handover_notes',
        'stock_audit',
        'is_billing_locked',
        'locked_billing_amount',
        'locked_billing_at',
        'is_print_locked',
        'locked_print_amount',
        'locked_print_pages',
        'locked_print_at',
        'status',
        'deposit_status',
        'deposited_amount',
        'retained_drawer_cash',
        'deposit_method',
        'deposit_recipient',
        'deposit_notes',
        'deposited_at',
        'deposit_verified_at',
        'deposit_verified_by',
        'closed_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'total_fnb_sales' => 'decimal:2',
        'total_internet_sales' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'cash_in_drawer' => 'decimal:2',
        'system_expected_cash' => 'decimal:2',
        'blind_cash_count' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'cash_denominations' => 'array',
        'non_cash_amount' => 'decimal:2',
        'stock_audit' => 'array',
        'is_billing_locked' => 'boolean',
        'locked_billing_amount' => 'decimal:2',
        'locked_billing_at' => 'datetime',
        'is_print_locked' => 'boolean',
        'locked_print_amount' => 'decimal:2',
        'locked_print_pages' => 'integer',
        'locked_print_at' => 'datetime',
        'deposited_amount' => 'decimal:2',
        'retained_drawer_cash' => 'decimal:2',
        'deposited_at' => 'datetime',
        'deposit_verified_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function depositMethodLabel(): string
    {
        return match ($this->deposit_method) {
            'cash_collector' => 'Tunai ke Kolektor / SPV',
            'safe_dropbox' => 'Brankas / Drop Box Outlet',
            'bank_transfer' => 'Transfer Rekening Bank',
            'next_shift_handover' => 'Serah Terima Kasir Berikutnya',
            default => $this->deposit_method ?: '-',
        };
    }

    public function shiftNumber(): int
    {
        $code = (string) ($this->shift?->code ?? '');
        if (preg_match('/(\d+)/', $code, $m)) {
            return max(1, min(4, (int) $m[1]));
        }

        $name = (string) ($this->shift?->name ?? '');
        if (preg_match('/(\d+)/', $name, $m)) {
            return max(1, min(4, (int) $m[1]));
        }

        return max(1, min(4, (int) ($this->shift_id ?? 1)));
    }

    public function rekapNumber(): string
    {
        $datePart = $this->work_date ? $this->work_date->format('Ymd') : now()->format('Ymd');
        $branchPart = (int) ($this->branch_id ?? 1);
        $shiftPart = $this->shiftNumber();
        $idPart = str_pad((string) ($this->id ?? 1), 4, '0', STR_PAD_LEFT);

        return "{$datePart}-B{$branchPart}-S{$shiftPart}-{$idPart}";
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
