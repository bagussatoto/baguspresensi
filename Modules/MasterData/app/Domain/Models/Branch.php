<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use App\Domains\Attendance\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'code',
        'name',
        'is_headquarters',
        'address',
        'latitude',
        'longitude',
        'radius_meters',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_headquarters' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'radius_meters' => 'integer',
    ];

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'branch_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'branch_id');
    }
}
