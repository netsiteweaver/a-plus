<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRelatedProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'related_product_id' => ['required', 'integer', 'different:product', 'exists:products,id'],
            'relation_type' => ['sometimes', 'string', 'in:related,upsell,accessory,replacement,bundle'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'product' => $this->route('product')?->id ?? $this->route('product'),
        ]);
    }
}
