<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'type' => ['sometimes', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'status' => ['sometimes', 'string', 'in:draft,published,archived'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'is_visible' => ['sometimes', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => str($this->input('name'))->slug()->toString(),
            ]);
        }
    }
}
