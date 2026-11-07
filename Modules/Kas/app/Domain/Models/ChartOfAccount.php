<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\MasterData\Domain\Models\Branch;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'branch_id',
        'account_code',
        'account_name',
        'account_category',
        'normal_balance',
        'parent_id',
        'level',
        'is_header',
        'is_active',
        'description',
        'current_balance',
    ];

    protected $casts = [
        'account_category' => AccountCategory::class,
        'normal_balance' => NormalBalance::class,
        'level' => 'integer',
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'current_balance' => 'float',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('account_code');
    }

    public function kasAccounts(): HasMany
    {
        return $this->hasMany(KasAccount::class, 'chart_of_account_id');
    }

    /**
     * Scope untuk akun aktif saja.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk akun detail yang bisa diposting transaksi (bukan header murni).
     */
    public function scopePostable($query)
    {
        return $query->where('is_header', false);
    }

    /**
     * Scope filter berdasarkan kategori.
     */
    public function scopeCategory($query, AccountCategory|string $category)
    {
        $val = $category instanceof AccountCategory ? $category->value : $category;

        return $query->where('account_category', $val);
    }

    /**
     * Menghitung total saldo termasuk akun anak jika merupakan header.
     */
    public function getEffectiveBalanceAttribute(): float
    {
        if (! $this->is_header) {
            return (float) $this->current_balance;
        }

        return (float) $this->children()->sum('current_balance');
    }
}
