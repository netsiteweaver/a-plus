<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductAttributeValueRequest;
use App\Http\Requests\Admin\UpdateProductAttributeValueRequest;
use App\Http\Resources\Admin\ProductAttributeValueResource;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ProductAttributeValueController extends AdminController
{
    public function index(Product $product): AnonymousResourceCollection
    {
        $values = $product->attributeValues()->with(['attribute', 'attributeValue'])->get();

        return ProductAttributeValueResource::collection($values);
    }

    public function store(StoreProductAttributeValueRequest $request, Product $product): ProductAttributeValueResource
    {
        $data = $request->validated();

        $this->ensureAttributeValueMatchesAttribute($data['attribute_id'], $data['attribute_value_id'] ?? null);

        $value = ProductAttributeValue::updateOrCreate(
            [
                'product_id' => $product->id,
                'attribute_id' => $data['attribute_id'],
                'attribute_value_id' => $data['attribute_value_id'] ?? null,
            ],
            [
                'value_text' => $data['value_text'] ?? null,
                'value_number' => $data['value_number'] ?? null,
                'value_json' => $data['value_json'] ?? null,
            ],
        );

        return ProductAttributeValueResource::make($value->load(['attribute', 'attributeValue']));
    }

    public function show(Product $product, ProductAttributeValue $attributeValue): ProductAttributeValueResource
    {
        $this->ensureValueBelongsToProduct($product, $attributeValue);

        return ProductAttributeValueResource::make($attributeValue->load(['attribute', 'attributeValue']));
    }

    public function update(UpdateProductAttributeValueRequest $request, Product $product, ProductAttributeValue $attributeValue): ProductAttributeValueResource
    {
        $this->ensureValueBelongsToProduct($product, $attributeValue);

        $data = $request->validated();

        $this->ensureAttributeValueMatchesAttribute($attributeValue->attribute_id, $data['attribute_value_id'] ?? null);

        $attributeValue->update($data);

        return ProductAttributeValueResource::make($attributeValue->fresh()->load(['attribute', 'attributeValue']));
    }

    public function destroy(Product $product, ProductAttributeValue $attributeValue): Response
    {
        $this->ensureValueBelongsToProduct($product, $attributeValue);

        $attributeValue->delete();

        return response()->noContent();
    }

    protected function ensureValueBelongsToProduct(Product $product, ProductAttributeValue $value): void
    {
        if ($value->product_id !== $product->id) {
            abort(404);
        }
    }

    protected function ensureAttributeValueMatchesAttribute(int $attributeId, ?int $attributeValueId): void
    {
        if (! $attributeValueId) {
            return;
        }

        $exists = AttributeValue::query()
            ->where('id', $attributeValueId)
            ->where('attribute_id', $attributeId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'attribute_value_id' => __('The selected attribute value is invalid for the given attribute.'),
            ]);
        }
    }
}
