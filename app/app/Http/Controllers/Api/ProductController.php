<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->with([
                'brand',
                'media' => fn ($q) => $q->orderBy('position'),
                'options.values' => fn ($q) => $q->orderBy('position'),
                'variants.optionValues.option',
                'defaultVariant',
                'attributeValues.attribute',
                'attributeValues.attributeValue',
                'relatedProducts.related.media',
                'relatedProducts.related.defaultVariant',
                'relatedProducts.related.attributeValues.attribute',
                'relatedProducts.related.attributeValues.attributeValue',
            ])
            ->firstOrFail();

        return response()->json([
            'product' => new ProductDetailResource($product),
        ]);
    }
}
