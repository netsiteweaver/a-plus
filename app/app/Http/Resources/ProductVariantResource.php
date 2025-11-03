<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'status' => $this->status,
            'price' => (float) $this->price,
            'compare_at_price' => $this->compare_at_price ? (float) $this->compare_at_price : null,
            'currency' => $this->currency,
            'inventory_quantity' => $this->inventory_quantity,
            'is_default' => (bool) $this->is_default,
            'option_values' => $this->optionValues->map(fn ($value) => [
                'option_code' => optional($value->option)->code,
                'value' => $value->value,
                'display_value' => $value->display_value,
            ]),
        ];
    }
}
