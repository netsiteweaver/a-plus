<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductOptionValueRequest;
use App\Http\Requests\Admin\UpdateProductOptionValueRequest;
use App\Http\Resources\Admin\ProductOptionValueResource;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductOptionValueController extends AdminController
{
    public function index(Product $product, ProductOption $option): AnonymousResourceCollection
    {
        $this->ensureOptionBelongsToProduct($product, $option);

        $values = $option->values()->orderBy('position')->get();

        return ProductOptionValueResource::collection($values);
    }

    public function store(StoreProductOptionValueRequest $request, Product $product, ProductOption $option): ProductOptionValueResource
    {
        $this->ensureOptionBelongsToProduct($product, $option);

        $data = $request->validated();

        $value = $option->values()->create([
            'value' => $data['value'],
            'display_value' => $data['display_value'] ?? null,
            'hex_value' => $data['hex_value'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'position' => $data['position'] ?? ($option->values()->max('position') + 1),
            'data' => $data['data'] ?? null,
        ]);

        return ProductOptionValueResource::make($value);
    }

    public function show(Product $product, ProductOption $option, ProductOptionValue $value): ProductOptionValueResource
    {
        $this->ensureOptionBelongsToProduct($product, $option);
        $this->ensureValueBelongsToOption($option, $value);

        return ProductOptionValueResource::make($value);
    }

    public function update(UpdateProductOptionValueRequest $request, Product $product, ProductOption $option, ProductOptionValue $value): ProductOptionValueResource
    {
        $this->ensureOptionBelongsToProduct($product, $option);
        $this->ensureValueBelongsToOption($option, $value);

        $value->update($request->validated());

        return ProductOptionValueResource::make($value->fresh());
    }

    public function destroy(Product $product, ProductOption $option, ProductOptionValue $value): Response
    {
        $this->ensureOptionBelongsToProduct($product, $option);
        $this->ensureValueBelongsToOption($option, $value);

        $value->variants()->detach();
        $value->delete();

        return response()->noContent();
    }

    protected function ensureOptionBelongsToProduct(Product $product, ProductOption $option): void
    {
        if ($option->product_id !== $product->id) {
            abort(404);
        }
    }

    protected function ensureValueBelongsToOption(ProductOption $option, ProductOptionValue $value): void
    {
        if ($value->product_option_id !== $option->id) {
            abort(404);
        }
    }
}
