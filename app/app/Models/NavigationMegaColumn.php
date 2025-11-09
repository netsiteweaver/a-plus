<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationMegaColumn extends Model
{
    protected $fillable = [
        'navigation_item_id',
        'heading',
        'position',
    ];

    /**
     * Get the navigation item this column belongs to
     */
    public function navigationItem()
    {
        return $this->belongsTo(NavigationItem::class, 'navigation_item_id');
    }

    /**
     * Get items in this column
     */
    public function items()
    {
        return $this->hasMany(NavigationMegaItem::class, 'mega_column_id')
            ->orderBy('position');
    }
}
