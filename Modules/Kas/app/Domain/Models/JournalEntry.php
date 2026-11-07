<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Model Jurnal Umum (General Journal Entry) Akuntansi Double-Entry.
 */
class JournalEntry extends Model
{
    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'branch_id',
        'creator_employee_id',
        'description',
        'status',
        'total_debit',
        'total_credit',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'float',
        'total_credit' => 'float',
    ];

    /**
     * Rincian baris debit & kredit pada jurnal ini.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    /**
     * Cabang / outlet yang bersangkutan.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Karyawan pembuat entri.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'creator_employee_id');
    }

    /**
     * Periksa apakah entri jurnal seimbang (Debit == Kredit).
     */
    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.001;
    }
}
