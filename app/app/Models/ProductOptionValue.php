<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOptionValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_option_id',
        'value',
        'display_value',
        'hex_value',
        'thumbnail_url',
        'position',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_option_value')
            ->withTimestamps();
    }
}
