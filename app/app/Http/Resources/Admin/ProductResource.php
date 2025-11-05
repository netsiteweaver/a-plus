<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'type' => $this->type,
            'brand_id' => $this->brand_id,
            'brand' => $this->whenLoaded('brand', fn () => [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ]),
            'default_variant_id' => $this->default_variant_id,
            'name' => $this->name,
            'subtitle' => $this->subtitle,
            'sku' => $this->sku,
            'excerpt' => $this->excerpt,
            'description' => $this->description,
            'specifications' => $this->specifications,
            'data' => $this->data,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'is_primary' => (bool) $category->pivot->is_primary,
                        'position' => $category->pivot->position,
                    ];
                });
            }),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants', fn () => $this->variants->sortByDesc('is_default'))),
            'options' => ProductOptionResource::collection($this->whenLoaded('options', fn () => $this->options->sortBy('position'))),
            'media' => ProductMediaResource::collection($this->whenLoaded('media', fn () => $this->media->sortBy('position'))),
            'attribute_values' => ProductAttributeValueResource::collection($this->whenLoaded('attributeValues')),
            'related_products' => RelatedProductResource::collection($this->whenLoaded('relatedProducts', fn () => $this->relatedProducts->sortBy('position'))),
        ];
    }
}
