<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'slug',
        'type',
        'brand_id',
        'default_variant_id',
        'name',
        'subtitle',
        'sku',
        'excerpt',
        'description',
        'specifications',
        'data',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'specifications' => 'array',
        'data' => 'array',
        'published_at' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)
            ->withPivot(['is_primary', 'position'])
            ->withTimestamps();
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'default_variant_id');
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function relatedProducts()
    {
        return $this->hasMany(RelatedProduct::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
