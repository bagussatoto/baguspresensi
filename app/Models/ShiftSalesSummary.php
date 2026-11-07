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
        'handover_amount',
        'handover_recipient',
        'handover_status',
        'handover_at',
        'handover_by',
        'stock_audit',
        'is_billing_locked',
        'locked_billing_amount',
        'raw_billing_amount',
        'billing_adjustment_amount',
        'billing_adjustment_reason',
        'billing_adjustment_notes',
        'billing_blackout_minutes',
        'billing_blackout_pcs',
        'billing_adjusted_by',
        'billing_adjustment_status',
        'billing_adjustment_approved_by',
        'billing_adjustment_approved_at',
        'billing_adjustment_rejected_by',
        'billing_adjustment_rejected_at',
        'billing_adjustment_rejection_reason',
        'locked_billing_at',
        'is_print_locked',
        'locked_print_amount',
        'locked_print_pages',
        'locked_print_at',
        'raw_print_pages',
        'raw_print_amount',
        'print_adjustment_pages',
        'print_adjustment_amount',
        'print_adjustment_reason',
        'print_adjustment_notes',
        'print_adjusted_by',
        'print_adjustment_status',
        'print_adjustment_approved_by',
        'print_adjustment_approved_at',
        'print_adjustment_rejected_by',
        'print_adjustment_rejected_at',
        'print_adjustment_rejection_reason',
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
        'handover_amount' => 'decimal:2',
        'handover_at' => 'datetime',
        'is_billing_locked' => 'boolean',
        'locked_billing_amount' => 'decimal:2',
        'raw_billing_amount' => 'decimal:2',
        'billing_adjustment_amount' => 'decimal:2',
        'billing_blackout_minutes' => 'integer',
        'billing_blackout_pcs' => 'integer',
        'billing_adjustment_approved_at' => 'datetime',
        'billing_adjustment_rejected_at' => 'datetime',
        'locked_billing_at' => 'datetime',
        'is_print_locked' => 'boolean',
        'locked_print_amount' => 'decimal:2',
        'locked_print_pages' => 'integer',
        'locked_print_at' => 'datetime',
        'raw_print_pages' => 'integer',
        'raw_print_amount' => 'decimal:2',
        'print_adjustment_pages' => 'integer',
        'print_adjustment_amount' => 'decimal:2',
        'print_adjustment_approved_at' => 'datetime',
        'print_adjustment_rejected_at' => 'datetime',
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

    public function handoverAmount(): float
    {
        if ($this->handover_amount !== null && (float) $this->handover_amount > 0) {
            return (float) $this->handover_amount;
        }

        if ($this->deposit_method === 'next_shift_handover' && $this->deposited_amount !== null && (float) $this->deposited_amount > 0) {
            return (float) $this->deposited_amount;
        }

        return (float) ($this->blind_cash_count ?? $this->cash_in_drawer ?? $this->total_revenue ?? 0);
    }

    public function handoverRecipientName(): string
    {
        return $this->handover_recipient
            ?: ($this->deposit_method === 'next_shift_handover' && $this->deposit_recipient ? $this->deposit_recipient : 'Kasir Shift Berikutnya');
    }

    public function hasBeenDeposited(): bool
    {
        return in_array($this->deposit_status, ['verified', 'pending_verification'], true) && $this->deposit_method !== 'next_shift_handover';
    }

    public function hasBillingAdjustment(): bool
    {
        return (float) ($this->billing_adjustment_amount ?? 0) > 0;
    }

    public function isBillingAdjustmentApproved(): bool
    {
        return $this->hasBillingAdjustment() && $this->billing_adjustment_status === 'approved';
    }

    public function isBillingAdjustmentPending(): bool
    {
        return $this->hasBillingAdjustment() && $this->billing_adjustment_status === 'pending_approval';
    }

    public function hasPrintAdjustment(): bool
    {
        return (float) ($this->print_adjustment_amount ?? 0) > 0 || (int) ($this->print_adjustment_pages ?? 0) > 0;
    }

    public function isPrintAdjustmentApproved(): bool
    {
        return $this->hasPrintAdjustment() && $this->print_adjustment_status === 'approved';
    }

    public function isPrintAdjustmentPending(): bool
    {
        return $this->hasPrintAdjustment() && $this->print_adjustment_status === 'pending_approval';
    }

    public function isBillingAdjustmentRejected(): bool
    {
        return $this->billing_adjustment_status === 'rejected';
    }

    /**
     * Setujui penyesuaian billing: potongan resmi memotong omset internet shift.
     */
    public function approveBillingAdjustment(string $approverName): void
    {
        $rawAmount = (float) ($this->raw_billing_amount ?? $this->locked_billing_amount ?? 0);
        $netAmount = max(0, $rawAmount - (float) ($this->billing_adjustment_amount ?? 0));
        $now = now();

        $this->billing_adjustment_status = 'approved';
        $this->billing_adjustment_approved_by = $approverName;
        $this->billing_adjustment_approved_at = $now;
        $this->is_billing_locked = true;
        $this->locked_billing_amount = $netAmount;
        $this->locked_billing_at = $now;
        $this->total_internet_sales = $netAmount;
        $this->total_revenue = (float) $this->total_fnb_sales + $netAmount;
        $this->save();
    }

    /**
     * Tolak penyesuaian billing: omset internet kembali penuh tanpa potongan.
     */
    public function rejectBillingAdjustment(string $rejectorName, string $reason): void
    {
        $rawAmount = (float) ($this->raw_billing_amount ?? $this->locked_billing_amount ?? 0);

        $this->billing_adjustment_status = 'rejected';
        $this->billing_adjustment_rejected_by = $rejectorName;
        $this->billing_adjustment_rejected_at = now();
        $this->billing_adjustment_rejection_reason = $reason;
        $this->is_billing_locked = true;
        $this->locked_billing_amount = $rawAmount;
        $this->total_internet_sales = $rawAmount;
        $this->total_revenue = (float) $this->total_fnb_sales + $rawAmount;
        $this->save();
    }

    public function billingAdjustmentStatusLabel(): string
    {
        return match ($this->billing_adjustment_status) {
            'approved' => 'Disetujui Admin/Supervisor',
            'pending_approval' => 'Menunggu Persetujuan Admin',
            'rejected' => 'Ditolak Admin',
            default => 'Tidak Ada',
        };
    }

    public function billingAdjustmentReasonLabel(): string
    {
        return match ($this->billing_adjustment_reason) {
            'blackout' => '⚡ Pemadaman Listrik PLN',
            'isp_down' => '🌐 Gangguan ISP / Jaringan Down',
            'hardware_crash' => '💻 PC Freeze / Crash Massal',
            'customer_refund' => '🔄 Refund / Kompensasi Waktu Pelanggan',
            'manual_correction' => '✏️ Koreksi Selisih Kasir Manual',
            default => $this->billing_adjustment_reason ?: 'Penyesuaian Operasional',
        };
    }

    /**
     * Setujui penyesuaian print kertas: potongan lembar/nominal resmi memotong target pendapatan print shift.
     */
    public function approvePrintAdjustment(string $approverName): void
    {
        $rawAmount = (float) ($this->raw_print_amount ?? $this->locked_print_amount ?? 0);
        $rawPages = (int) ($this->raw_print_pages ?? $this->locked_print_pages ?? 0);
        $adjAmount = (float) ($this->print_adjustment_amount ?? 0);
        $adjPages = (int) ($this->print_adjustment_pages ?? 0);

        $netAmount = max(0, $rawAmount - $adjAmount);
        $netPages = max(0, $rawPages - $adjPages);
        $now = now();

        $this->print_adjustment_status = 'approved';
        $this->print_adjustment_approved_by = $approverName;
        $this->print_adjustment_approved_at = $now;
        $this->is_print_locked = true;
        $this->locked_print_amount = $netAmount;
        $this->locked_print_pages = $netPages;
        $this->locked_print_at = $now;
        $this->save();
    }

    /**
     * Tolak penyesuaian print kertas: target omset print kembali penuh tanpa potongan.
     */
    public function rejectPrintAdjustment(string $rejectorName, string $reason): void
    {
        $rawAmount = (float) ($this->raw_print_amount ?? $this->locked_print_amount ?? 0);
        $rawPages = (int) ($this->raw_print_pages ?? $this->locked_print_pages ?? 0);

        $this->print_adjustment_status = 'rejected';
        $this->print_adjustment_rejected_by = $rejectorName;
        $this->print_adjustment_rejected_at = now();
        $this->print_adjustment_rejection_reason = $reason;
        $this->is_print_locked = true;
        $this->locked_print_amount = $rawAmount;
        $this->locked_print_pages = $rawPages;
        $this->save();
    }

    public function printAdjustmentStatusLabel(): string
    {
        return match ($this->print_adjustment_status) {
            'approved' => 'Disetujui Admin/Supervisor',
            'pending_approval' => 'Menunggu Persetujuan Admin',
            'rejected' => 'Ditolak Admin',
            default => 'Tidak Ada',
        };
    }

    public function printAdjustmentReasonLabel(): string
    {
        return match ($this->print_adjustment_reason) {
            'paper_jam' => '📄 Kertas Macet / Tersangkut (Paper Jam)',
            'bad_ink' => '💧 Tinta Rusak / Bergaris / Pudar',
            'internal_use' => '🏢 Dokumen Operasional Toko Sendiri',
            'wrong_doc' => '❌ Salah Dokumen / Salah Cetak Pelanggan',
            default => $this->print_adjustment_reason ?: 'Penyesuaian Print',
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

    public function invoiceNumber(): string
    {
        $datePart = $this->work_date ? $this->work_date->format('Ymd') : now()->format('Ymd');
        $branchPart = (int) ($this->branch_id ?? 1);
        $shiftPart = $this->shiftNumber();
        $idPart = (int) ($this->id ?? 1);

        return "{$datePart}-{$branchPart}r-{$idPart}-{$shiftPart}";
    }

    public function depositReceiptNumber(): string
    {
        $idPart = (int) ($this->id ?? 1);
        $branchPart = (int) ($this->branch_id ?? 1);

        return sprintf('%03d-%02d-%04d', ($branchPart * 220) + 8, 49, 2900 + $idPart);
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

    public function depositProofUrl(): ?string
    {
        $notes = (string) ($this->deposit_notes ?? $this->handover_notes ?? '');
        if (preg_match('/\[Bukti Setor: ([^\]]+)\]/', $notes, $matches)) {
            return asset(trim($matches[1]));
        }

        return null;
    }
}
