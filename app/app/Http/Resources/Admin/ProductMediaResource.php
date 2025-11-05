<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ProductMedia
 */
class ProductMediaResource extends JsonResource
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
            'product_variant_id' => $this->product_variant_id,
            'type' => $this->type,
            'disk' => $this->disk,
            'path' => $this->path,
            'url' => $this->url,
            'is_primary' => $this->is_primary,
            'position' => $this->position,
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
