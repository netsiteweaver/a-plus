<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ProductVariant
 */
class ProductVariantResource extends JsonResource
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
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'status' => $this->status,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'cost' => $this->cost,
            'currency' => $this->currency,
            'inventory_sku' => $this->inventory_sku,
            'inventory_policy' => $this->inventory_policy,
            'inventory_quantity' => $this->inventory_quantity,
            'track_inventory' => $this->track_inventory,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'dimension_unit' => $this->dimension_unit,
            'is_default' => $this->is_default,
            'requires_shipping' => $this->requires_shipping,
            'requires_serial' => $this->requires_serial,
            'published_at' => $this->published_at,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'option_values' => $this->whenLoaded('optionValues', function () {
                return $this->optionValues->map(function ($value) {
                    return [
                        'id' => $value->id,
                        'product_option_id' => $value->product_option_id,
                        'value' => $value->value,
                        'display_value' => $value->display_value,
                        'hex_value' => $value->hex_value,
                    ];
                });
            }),
        ];
    }
}
