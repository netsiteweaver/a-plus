<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCardResource;
use App\Http\Resources\ProductCardResource;
use App\Models\Category;
use App\Models\Product;

class CatalogHomeController extends Controller
{
    public function __invoke()
    {
        $categories = Category::query()
            ->where('is_visible', true)
            ->orderBy('position')
            ->take(3)
            ->get();

        $productPool = Product::published()
            ->with([
                'media' => fn ($query) => $query->orderBy('position'),
                'defaultVariant',
                'variants',
                'attributeValues.attribute',
                'attributeValues.attributeValue',
            ])
            ->inRandomOrder()
            ->take(16)
            ->get();

        $heroProduct = $productPool->first();

        $dailyDeals = $productPool->filter(function (Product $product) {
            return str_contains(strtolower($product->data['badge'] ?? ''), 'save')
                || str_contains(strtolower($product->data['badge'] ?? ''), 'bundle');
        })->take(2);

        if ($dailyDeals->count() < 2) {
            $dailyDeals = $productPool->take(2);
        }

        $featuredProducts = $productPool->take(8);

        return response()->json([
            'hero' => $heroProduct ? [
                'title' => $heroProduct->name,
                'subtitle' => $heroProduct->subtitle ?? $heroProduct->excerpt,
                'description' => $heroProduct->excerpt,
                'badge' => $heroProduct->data['badge'] ?? null,
                'cta' => [
                    'label' => 'Shop now',
                    'to' => '/product/' . $heroProduct->slug,
                ],
                'image' => optional($heroProduct->media->sortBy('position')->first())->url,
            ] : null,
            'featured_categories' => CategoryCardResource::collection($categories),
            'daily_deals' => ProductCardResource::collection($dailyDeals),
            'featured_products' => ProductCardResource::collection($featuredProducts),
        ]);
    }
}
