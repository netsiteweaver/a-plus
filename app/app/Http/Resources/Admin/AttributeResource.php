<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Attribute
 */
class AttributeResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'unit' => $this->unit,
            'is_filterable' => $this->is_filterable,
            'is_required' => $this->is_required,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'values' => AttributeValueResource::collection($this->whenLoaded('values')->sortBy('value')),
        ];
    }
}
