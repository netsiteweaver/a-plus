<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttributeValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        $attributeId = $this->route('attribute')?->id ?? $this->route('attribute');

        return [
            'value' => ['required', 'string', 'max:255', Rule::unique('attribute_values', 'value')->where('attribute_id', $attributeId)],
            'display_value' => ['nullable', 'string', 'max:255'],
            'numeric_value' => ['nullable', 'numeric'],
            'data' => ['nullable', 'array'],
        ];
    }
}
