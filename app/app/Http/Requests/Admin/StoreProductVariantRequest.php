<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:120', 'unique:product_variants,sku'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:draft,published,archived'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'inventory_sku' => ['nullable', 'string', 'max:120'],
            'inventory_policy' => ['sometimes', 'string', 'in:deny,allow'],
            'inventory_quantity' => ['sometimes', 'integer', 'min:0'],
            'track_inventory' => ['sometimes', 'boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', 'string', 'max:10'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'dimension_unit' => ['nullable', 'string', 'max:10'],
            'is_default' => ['sometimes', 'boolean'],
            'requires_shipping' => ['sometimes', 'boolean'],
            'requires_serial' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'data' => ['nullable', 'array'],
            'option_value_ids' => ['nullable', 'array'],
            'option_value_ids.*' => ['integer', 'exists:product_option_values,id'],
        ];
    }
}
