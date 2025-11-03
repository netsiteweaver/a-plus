<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelatedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'related_product_id',
        'relation_type',
        'position',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function related()
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }
}
