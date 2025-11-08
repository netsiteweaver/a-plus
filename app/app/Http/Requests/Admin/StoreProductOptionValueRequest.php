<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductOptionValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        $optionId = $this->route('option')?->id ?? $this->route('option');

        return [
            'value' => ['required', 'string', 'max:255', Rule::unique('product_option_values', 'value')->where('product_option_id', $optionId)],
            'display_value' => ['nullable', 'string', 'max:255'],
            'hex_value' => ['nullable', 'string', 'max:10'],
            'thumbnail_url' => ['nullable', 'url', 'max:255'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'data' => ['nullable', 'array'],
        ];
    }
}
