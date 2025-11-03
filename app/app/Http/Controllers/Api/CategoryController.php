<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryDetailResource;
use App\Http\Resources\ProductCardResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->with([
                'products' => function ($query) {
                    $query->published()
                        ->with([
                            'media' => fn ($q) => $q->orderBy('position'),
                            'defaultVariant',
                            'variants',
                            'attributeValues.attribute',
                            'attributeValues.attributeValue',
                        ]);
                },
            ])
            ->firstOrFail();

        $filters = $this->buildFilters($category);

        return response()->json([
            'category' => new CategoryDetailResource($category),
            'products' => ProductCardResource::collection($category->products),
            'filters' => $filters,
        ]);
    }

    protected function buildFilters(Category $category): array
    {
        $products = $category->products;

        if ($products->isEmpty()) {
            return [];
        }

        $filters = [];

        $attributeGroups = $products
            ->flatMap(fn ($product) => $product->attributeValues)
            ->loadMissing(['attribute', 'attributeValue'])
            ->groupBy(fn ($value) => optional($value->attribute)->code)
            ->reject(fn ($values, $code) => $code === null);

        foreach ($attributeGroups as $code => $values) {
            $attribute = optional($values->first())->attribute;
            if (! $attribute) {
                continue;
            }

            $filters[] = [
                'label' => $attribute->name,
                'key' => $code,
                'options' => $values
                    ->groupBy(fn ($value) => optional($value->attributeValue)->id ?: $value->value_text)
                    ->map(function ($group, $key) {
                        $first = $group->first();
                        $display = optional($first->attributeValue)->display_value ?? $first->value_text;

                        return [
                            'label' => $display,
                            'value' => Str::slug($display ?? (string) $key),
                            'count' => $group->count(),
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        }

        $priceMin = $products->map(fn ($product) => optional($product->defaultVariant)->price ?? $product->variants->min('price'))->filter()->min();
        $priceMax = $products->map(fn ($product) => optional($product->defaultVariant)->price ?? $product->variants->max('price'))->filter()->max();

        if ($priceMin && $priceMax) {
            $filters[] = [
                'label' => 'Price',
                'key' => 'price',
                'range' => [
                    'min' => floor($priceMin),
                    'max' => ceil($priceMax),
                ],
            ];
        }

        return $filters;
    }
}
