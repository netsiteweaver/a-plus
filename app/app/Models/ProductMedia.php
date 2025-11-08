<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProductMedia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'type',
        'disk',
        'path',
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

    protected $appends = ['url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function url(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->path) {
                    return null;
                }

                // If path is already a full URL (http/https), return it directly
                if (preg_match('/^https?:\/\//', $this->path)) {
                    return $this->path;
                }

                // Otherwise, generate URL from storage disk and path
                if ($this->disk) {
                    return Storage::disk($this->disk)->url($this->path);
                }

                return null;
            }
        );
    }
}
