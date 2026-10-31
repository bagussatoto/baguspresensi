<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

class PrinterPrintJob extends Model
{
    protected $table = 'printer_print_jobs';

    protected $fillable = [
        'branch_id',
        'employee_id',
        'printer_name',
        'document_name',
        'client_computer',
        'pages',
        'color_mode',
        'unit_price',
        'total_amount',
        'work_date',
        'ip_address',
        'printed_at',
    ];

    protected $casts = [
        'pages' => 'integer',
        'unit_price' => 'float',
        'total_amount' => 'float',
        'work_date' => 'date',
        'printed_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
