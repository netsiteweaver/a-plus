<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionValueResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'display_value' => $this->display_value,
            'hex_value' => $this->hex_value,
            'thumbnail_url' => $this->thumbnail_url,
            'position' => $this->position,
        ];
    }
}
