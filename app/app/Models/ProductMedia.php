<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMedia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'type',
        'disk',
        'path',
        'url',
        'is_primary',
        'position',
        'alt_text',
        'caption',
        'data',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'data' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
