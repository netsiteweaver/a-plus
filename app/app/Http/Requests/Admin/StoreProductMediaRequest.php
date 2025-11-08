<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:image,video,document'],
            'disk' => ['nullable', 'string', 'max:120'],
            'path' => ['required', 'string', 'max:2048'],
            'url' => ['nullable', 'url', 'max:2048'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'is_primary' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
        ];
    }
}
