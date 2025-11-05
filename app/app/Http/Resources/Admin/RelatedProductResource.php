<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\RelatedProduct
 */
class RelatedProductResource extends JsonResource
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
            'product_id' => $this->product_id,
            'related_product_id' => $this->related_product_id,
            'relation_type' => $this->relation_type,
            'position' => $this->position,
            'related_product' => $this->whenLoaded('related', function () {
                return [
                    'id' => $this->related->id,
                    'name' => $this->related->name,
                    'slug' => $this->related->slug,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
