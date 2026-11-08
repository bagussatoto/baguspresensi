<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Kas\Domain\Models\JournalEntry;

/**
 * Model StockOpname: Sesi Audit & Hitung Fisik Persediaan Berkala Cabang.
 */
class StockOpname extends Model
{
    protected $table = 'stock_opnames';

    protected $fillable = [
        'opname_number',
        'branch_id',
        'warehouse_id',
        'opname_date',
        'conducted_by',
        'approved_by',
        'status',
        'total_items_counted',
        'total_discrepancy_items',
        'total_variance_cost',
        'notes',
        'completed_at',
        'approved_at',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'total_items_counted' => 'integer',
        'total_discrepancy_items' => 'integer',
        'total_variance_cost' => 'float',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'conducted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class, 'stock_opname_id');
    }

    public function journalEntry(): HasOne
    {
        return $this->hasOne(JournalEntry::class, 'reference_id')
            ->where('reference_type', self::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isAdjusted(): bool
    {
        return $this->status === 'adjusted';
    }
}
