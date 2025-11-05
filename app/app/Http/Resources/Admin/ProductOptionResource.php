<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ProductOption
 */
class ProductOptionResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'input_type' => $this->input_type,
            'is_required' => $this->is_required,
            'position' => $this->position,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'values' => ProductOptionValueResource::collection($this->whenLoaded('values')->sortBy('position')),
        ];
    }
}
