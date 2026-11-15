<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Branch;

/**
 * Model Periode Tutup Buku & Penguncian Akuntansi (Accounting Period Closing).
 */
class AccountingPeriod extends Model
{
    use HasFactory;

    protected $table = 'accounting_periods';

    protected $fillable = [
        'branch_id',
        'year',
        'month',
        'period_name',
        'start_date',
        'end_date',
        'status',
        'closed_by_user_id',
        'closed_at',
        'reopened_by_user_id',
        'reopened_at',
        'closing_journal_entry_id',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by_user_id');
    }

    public function closingJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'closing_journal_entry_id');
    }

    /**
     * Periksa apakah tanggal dan cabang tertentu berada dalam periode tutup buku yang terkunci.
     */
    public static function isDateLocked(string|Carbon $date, ?int $branchId = null): bool
    {
        return self::getLockedPeriodForDate($date, $branchId) !== null;
    }

    /**
     * Ambil record periode terkunci (jika ada) yang mencakup tanggal dan cabang tertentu.
     */
    public static function getLockedPeriodForDate(string|Carbon $date, ?int $branchId = null): ?self
    {
        $targetDate = Carbon::parse($date)->toDateString();

        return self::query()
            ->where('status', 'closed')
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->where(function ($query) use ($branchId): void {
                $query->whereNull('branch_id');
                if ($branchId !== null) {
                    $query->orWhere('branch_id', $branchId);
                }
            })
            ->first();
    }
}
