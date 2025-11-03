<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProductCardResource extends JsonResource
{
    public function toArray($request): array
    {
        $primaryMedia = $this->media->sortBy('position')->first();
        $variant = $this->defaultVariant ?? $this->variants->sortBy('price')->first();

        $attributeValues = $this->relationLoaded('attributeValues')
            ? $this->attributeValues
            : $this->attributeValues()->get();

        if ($attributeValues instanceof EloquentCollection) {
            $attributeValues->load('attribute', 'attributeValue');
        } else {
            $attributeValues = Collection::make($attributeValues);
        }

        $metaItems = $attributeValues
            ->take(3)
            ->map(function ($attributeValue) {
                return optional($attributeValue->attributeValue)->display_value ?? $attributeValue->value_text;
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
