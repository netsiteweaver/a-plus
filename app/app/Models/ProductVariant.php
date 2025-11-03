<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'status',
        'price',
        'compare_at_price',
        'cost',
        'currency',
        'inventory_sku',
        'inventory_policy',
        'inventory_quantity',
        'track_inventory',
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'dimension_unit',
        'is_default',
        'requires_shipping',
        'requires_serial',
        'published_at',
        'data',
    ];

    protected $casts = [
        'price' => 'float',
        'compare_at_price' => 'float',
        'cost' => 'float',
        'inventory_quantity' => 'int',
        'track_inventory' => 'boolean',
        'is_default' => 'boolean',
        'requires_shipping' => 'boolean',
        'requires_serial' => 'boolean',
        'published_at' => 'datetime',
        'data' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'product_variant_option_value')
            ->withTimestamps();
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }
}
