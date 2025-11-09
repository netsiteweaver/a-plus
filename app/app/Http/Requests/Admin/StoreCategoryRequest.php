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
            'is_featured' => ['sometimes', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
            'tree' => ['sometimes', 'boolean'], // Allow but ignore tree parameter
        ];
    }

    protected function prepareForValidation(): void
    {
        // Remove 'tree' parameter if present (it's for GET queries only)
        if ($this->has('tree')) {
            $data = $this->all();
            unset($data['tree']);
            $this->replace($data);
        }
        
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => str($this->input('name'))->slug()->toString(),
            ]);
        }
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Remove 'tree' from validated data as it's only for querying
        if (is_array($validated)) {
            unset($validated['tree']);
        }
        
        return $validated;
    }
}
