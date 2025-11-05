<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:120', 'unique:attributes,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:text,textarea,number,boolean,select,json'],
            'unit' => ['nullable', 'string', 'max:50'],
            'is_filterable' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
            'data' => ['nullable', 'array'],
        ];
    }
}
