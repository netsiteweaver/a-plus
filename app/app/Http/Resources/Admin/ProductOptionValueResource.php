<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ProductOptionValue
 */
class ProductOptionValueResource extends JsonResource
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
            'product_option_id' => $this->product_option_id,
            'value' => $this->value,
            'display_value' => $this->display_value,
            'hex_value' => $this->hex_value,
            'thumbnail_url' => $this->thumbnail_url,
            'position' => $this->position,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
