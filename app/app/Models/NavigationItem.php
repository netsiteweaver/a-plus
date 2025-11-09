<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'url',
        'icon',
        'description',
        'is_mega',
        'is_external',
        'is_active',
        'position',
        'hero',
    ];

    protected $casts = [
        'is_mega' => 'boolean',
        'is_external' => 'boolean',
        'is_active' => 'boolean',
        'hero' => 'array',
    ];

    /**
     * Get the menu this item belongs to
     */
    public function menu()
    {
        return $this->belongsTo(NavigationMenu::class, 'menu_id');
    }

    /**
     * Get parent item
     */
    public function parent()
    {
        return $this->belongsTo(NavigationItem::class, 'parent_id');
    }

    /**
     * Get child items
     */
    public function children()
    {
        return $this->hasMany(NavigationItem::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('position');
    }

    /**
     * Get mega menu columns
     */
    public function megaColumns()
    {
        return $this->hasMany(NavigationMegaColumn::class, 'navigation_item_id')
            ->orderBy('position');
    }

    /**
     * Scope to filter active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter top-level items
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
