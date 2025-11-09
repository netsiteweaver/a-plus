<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationMegaItem extends Model
{
    protected $fillable = [
        'mega_column_id',
        'label',
        'url',
        'position',
    ];

    /**
     * Get the mega column this item belongs to
     */
    public function megaColumn()
    {
        return $this->belongsTo(NavigationMegaColumn::class, 'mega_column_id');
    }
}
