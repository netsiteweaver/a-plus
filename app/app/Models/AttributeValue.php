<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attribute_id',
        'value',
        'display_value',
        'numeric_value',
        'data',
    ];

    protected $casts = [
        'numeric_value' => 'float',
        'data' => 'array',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}
