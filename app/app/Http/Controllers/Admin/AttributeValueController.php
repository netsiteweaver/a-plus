<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreAttributeValueRequest;
use App\Http\Requests\Admin\UpdateAttributeValueRequest;
use App\Http\Resources\Admin\AttributeValueResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AttributeValueController extends AdminController
{
    public function index(Attribute $attribute): AnonymousResourceCollection
    {
        $values = $attribute->values()->orderBy('value')->get();

        return AttributeValueResource::collection($values);
    }

    public function store(StoreAttributeValueRequest $request, Attribute $attribute): AttributeValueResource
    {
        $value = $attribute->values()->create($request->validated());

        return AttributeValueResource::make($value);
    }

    public function show(Attribute $attribute, AttributeValue $value): AttributeValueResource
    {
        $this->ensureValueBelongsToAttribute($attribute, $value);

        return AttributeValueResource::make($value);
    }

    public function update(UpdateAttributeValueRequest $request, Attribute $attribute, AttributeValue $value): AttributeValueResource
    {
        $this->ensureValueBelongsToAttribute($attribute, $value);

        $value->update($request->validated());

        return AttributeValueResource::make($value->fresh());
    }

    public function destroy(Attribute $attribute, AttributeValue $value): Response
    {
        $this->ensureValueBelongsToAttribute($attribute, $value);

        $value->delete();

        return response()->noContent();
    }

    protected function ensureValueBelongsToAttribute(Attribute $attribute, AttributeValue $value): void
    {
        if ($value->attribute_id !== $attribute->id) {
            abort(404);
        }
    }
}
