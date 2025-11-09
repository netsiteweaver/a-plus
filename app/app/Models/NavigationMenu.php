<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NavigationMenu extends Model
{
    protected $fillable = [
        'name',
        'location',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all items for this menu
     */
    public function items()
    {
        return $this->hasMany(NavigationItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->orderBy('position');
    }

    /**
     * Get all items including children
     */
    public function allItems()
    {
        return $this->hasMany(NavigationItem::class, 'menu_id');
    }

    /**
     * Get menu by location with items
     */
    public static function byLocation(string $location)
    {
        return Cache::remember("navigation:menu:{$location}", 3600, function () use ($location) {
            return static::with([
                'items.children',
                'items.megaColumns.items'
            ])
                ->where('location', $location)
                ->where('is_active', true)
                ->first();
        });
    }

    /**
     * Clear navigation cache
     */
    public static function clearCache(): void
    {
        $locations = static::pluck('location');
        foreach ($locations as $location) {
            Cache::forget("navigation:menu:{$location}");
        }
    }
}
