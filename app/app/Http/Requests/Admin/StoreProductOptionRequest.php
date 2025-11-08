<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'code' => ['required', 'string', 'max:120', Rule::unique('product_options', 'code')->where('product_id', $productId)],
            'name' => ['required', 'string', 'max:255'],
            'input_type' => ['sometimes', 'string', 'in:select,swatch,radio,checkbox'],
            'is_required' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'data' => ['nullable', 'array'],
        ];
    }
}
