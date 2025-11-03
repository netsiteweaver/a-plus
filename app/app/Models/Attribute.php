<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'unit',
        'is_filterable',
        'is_required',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function productValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}
