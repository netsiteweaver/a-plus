<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCardResource extends JsonResource
{
    public function toArray($request): array
    {
        $primaryMedia = $this->media->sortBy('position')->first();
        $variant = $this->defaultVariant ?? $this->variants->sortBy('price')->first();

        $metaItems = $this->attributeValues
            ->loadMissing(['attribute', 'attributeValue'])
            ->take(3)
            ->map(function ($attributeValue) {
                return $attributeValue->attributeValue->display_value ?? $attributeValue->value_text;
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'badge' => $this->data['badge'] ?? null,
            'rating' => (float) ($this->data['rating'] ?? 4.6),
            'rating_count' => (int) ($this->data['rating_count'] ?? 120),
            'price' => $variant ? (float) $variant->price : null,
            'compare_at_price' => $variant && $variant->compare_at_price ? (float) $variant->compare_at_price : null,
            'image' => $primaryMedia?->url,
            'meta' => $metaItems,
        ];
    }
}
