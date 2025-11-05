<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductOptionRequest;
use App\Http\Requests\Admin\UpdateProductOptionRequest;
use App\Http\Resources\Admin\ProductOptionResource;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductOptionController extends AdminController
{
    public function index(Product $product): AnonymousResourceCollection
    {
        $options = $product->options()->with('values')->orderBy('position')->get();

        return ProductOptionResource::collection($options);
    }

    public function store(StoreProductOptionRequest $request, Product $product): ProductOptionResource
    {
        $data = $request->validated();

        $option = $product->options()->create([
            'code' => $data['code'],
            'name' => $data['name'],
            'input_type' => $data['input_type'] ?? 'select',
            'is_required' => $data['is_required'] ?? true,
            'position' => $data['position'] ?? ($product->options()->max('position') + 1),
            'data' => $data['data'] ?? null,
        ]);

        return ProductOptionResource::make($option->fresh('values'));
    }

    public function show(Product $product, ProductOption $option): ProductOptionResource
    {
        $this->ensureOptionBelongsToProduct($product, $option);

        return ProductOptionResource::make($option->load('values'));
    }

    public function update(UpdateProductOptionRequest $request, Product $product, ProductOption $option): ProductOptionResource
    {
        $this->ensureOptionBelongsToProduct($product, $option);

        $option->update($request->validated());

        return ProductOptionResource::make($option->fresh('values'));
    }

    public function destroy(Product $product, ProductOption $option): Response
    {
        $this->ensureOptionBelongsToProduct($product, $option);

        $valueIds = $option->values()->pluck('id');
        if ($valueIds->isNotEmpty()) {
            $product->variants()->each(function ($variant) use ($valueIds) {
                $variant->optionValues()->detach($valueIds);
            });

            $option->values()->delete();
        }

        $option->delete();

        return response()->noContent();
    }

    protected function ensureOptionBelongsToProduct(Product $product, ProductOption $option): void
    {
        if ($option->product_id !== $product->id) {
            abort(404);
        }
    }
}
