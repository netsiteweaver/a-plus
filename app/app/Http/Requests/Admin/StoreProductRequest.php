<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'type' => ['sometimes', 'string', 'max:50'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:120', 'unique:products,sku'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
            'status' => ['sometimes', 'string', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'primary_category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => str($this->input('name'))->slug()->toString(),
            ]);
        }

        if ($this->filled('category_ids') && ! $this->filled('primary_category_id')) {
            $this->merge([
                'primary_category_id' => collect($this->input('category_ids'))->first(),
            ]);
        }
    }
}
