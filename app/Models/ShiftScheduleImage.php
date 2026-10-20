<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftScheduleImage extends Model
{
    protected $table = 'shift_schedule_images';

    protected $fillable = [
        'title',
        'image_path',
        'period',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * URL publik untuk gambar jadwal.
     */
    public function getImageUrlAttribute(): string
    {
        return asset($this->image_path);
    }
}
