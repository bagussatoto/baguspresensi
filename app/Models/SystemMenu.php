<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SystemMenu extends Model
{
    protected $table = 'system_menus';

    protected $fillable = [
        'group',
        'code',
        'name',
        'url',
        'route_name',
        'icon',
        'sort_order',
        'is_active',
        'target',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get active menus for a specific group sorted by sort_order.
     *
     * @return Collection<int, SystemMenu>
     */
    public static function getActiveByGroup(string $group = 'presensi2')
    {
        return static::where('group', $group)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }
}
