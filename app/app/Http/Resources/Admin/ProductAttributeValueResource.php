<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ProductAttributeValue
 */
class ProductAttributeValueResource extends JsonResource
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
            'attribute_id' => $this->attribute_id,
            'attribute' => $this->whenLoaded('attribute', function () {
                return [
                    'id' => $this->attribute->id,
                    'code' => $this->attribute->code,
                    'name' => $this->attribute->name,
                    'type' => $this->attribute->type,
                    'unit' => $this->attribute->unit,
                ];
            }),
            'attribute_value_id' => $this->attribute_value_id,
            'attribute_value' => $this->whenLoaded('attributeValue', function () {
                return [
                    'id' => $this->attributeValue->id,
                    'value' => $this->attributeValue->value,
                    'display_value' => $this->attributeValue->display_value,
                ];
            }),
            'value_text' => $this->value_text,
            'value_number' => $this->value_number,
            'value_json' => $this->value_json,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
