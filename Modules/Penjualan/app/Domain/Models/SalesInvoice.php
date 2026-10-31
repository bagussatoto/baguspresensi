<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;

class SalesInvoice extends Model
{
    protected $table = 'sales_invoices';

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'customer_id',
        'branch_id',
        'payment_status',
        'invoice_date',
        'due_date',
        'total_amount',
        'tax_amount',
        'net_amount',
        'paid_amount',
        'remaining_balance',
        'notes',
        'created_by',
        'snapshot_payload',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'float',
        'tax_amount' => 'float',
        'net_amount' => 'float',
        'paid_amount' => 'float',
        'remaining_balance' => 'float',
        'snapshot_payload' => 'array',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class, 'sales_invoice_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->remaining_balance <= 0.0;
    }

    /**
     * Catat pembayaran pelunasan piutang.
     */
    public function recordPayment(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->remaining_balance = max(0.0, $this->net_amount - $this->paid_amount);

        if ($this->remaining_balance <= 0.0) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0.0) {
            $this->payment_status = 'partial';
        }

        $this->save();
    }
}
