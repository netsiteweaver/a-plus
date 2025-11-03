<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductMediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'url' => $this->url,
            'is_primary' => (bool) $this->is_primary,
            'position' => $this->position,
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
        ];
    }
}
