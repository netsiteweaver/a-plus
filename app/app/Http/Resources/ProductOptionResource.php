<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'input_type' => $this->input_type,
            'position' => $this->position,
            'values' => ProductOptionValueResource::collection($this->whenLoaded('values')), 
        ];
    }
}
