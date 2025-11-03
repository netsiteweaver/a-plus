<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'type',
        'description',
        'image_url',
        'status',
        'position',
        'is_visible',
        'meta_title',
        'meta_description',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'is_visible' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['is_primary', 'position'])
            ->withTimestamps();
    }
}
