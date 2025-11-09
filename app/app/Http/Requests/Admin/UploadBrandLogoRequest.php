<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadBrandLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'logo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,gif,svg,webp',
                'max:2048', // 2MB max
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required' => 'Please select a logo image to upload.',
            'logo.image' => 'The file must be an image.',
            'logo.mimes' => 'The logo must be a file of type: jpeg, jpg, png, gif, svg, webp.',
            'logo.max' => 'The logo must not be larger than 2MB.',
        ];
    }
}

