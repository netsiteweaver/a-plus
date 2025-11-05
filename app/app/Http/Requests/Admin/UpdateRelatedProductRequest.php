<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRelatedProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'relation_type' => ['sometimes', 'string', 'in:related,upsell,accessory,replacement,bundle'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
