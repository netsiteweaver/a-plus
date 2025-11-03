<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryCardResource extends JsonResource
{
    /**
     * @param  \App\Models\Category  $resource
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image_url,
            'accent' => $this->data['accent'] ?? null,
        ];
    }
}
