<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Brand
 */
class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'website_url' => $this->website_url,
            'description' => $this->description,
            'logo_url' => $this->logo_url,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'data' => $this->data,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
