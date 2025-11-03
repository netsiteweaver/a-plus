<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $variant = $this->defaultVariant ?? $this->variants->sortBy('is_default')->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'brand' => $this->brand?->name,
            'badge' => $this->data['badge'] ?? null,
            'rating' => (float) ($this->data['rating'] ?? 4.6),
            'rating_count' => (int) ($this->data['rating_count'] ?? 120),
            'price' => $variant ? (float) $variant->price : null,
            'compare_at_price' => $variant && $variant->compare_at_price ? (float) $variant->compare_at_price : null,
            'sku' => $this->sku,
            'media' => ProductMediaResource::collection($this->whenLoaded('media')->sortBy('position')),
            'options' => ProductOptionResource::collection($this->whenLoaded('options')->sortBy('position')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')->sortByDesc('is_default')),
            'specifications' => $this->buildSpecificationGroups(),
            'related_products' => ProductCardResource::collection($this->relatedProducts->map->related->filter()),
        ];
    }

    protected function buildSpecificationGroups(): array
    {
        $groups = [];

        $mapping = [
            'processor' => 'Performance',
            'graphics' => 'Performance',
            'memory' => 'Performance',
            'display' => 'Display',
        ];

        $this->attributeValues->loadMissing(['attribute', 'attributeValue'])->each(function ($value) use (&$groups, $mapping) {
            $attribute = $value->attribute;
            if (! $attribute) {
                return;
            }

            $group = $mapping[$attribute->code] ?? 'Specifications';

            $groups[$group][] = [
                'label' => $attribute->name,
                'value' => $value->attributeValue->display_value ?? $value->value_text,
            ];
        });

        return collect($groups)
            ->map(fn ($items, $label) => [
                'label' => $label,
                'items' => collect($items)
                    ->filter(fn ($item) => filled($item['value']))
                    ->values(),
            ])
            ->values()
            ->all();
    }
}
