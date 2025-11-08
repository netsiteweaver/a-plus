<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreRelatedProductRequest;
use App\Http\Requests\Admin\UpdateRelatedProductRequest;
use App\Http\Resources\Admin\RelatedProductResource;
use App\Models\Product;
use App\Models\RelatedProduct;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class RelatedProductController extends AdminController
{
    public function index(Product $product): AnonymousResourceCollection
    {
        $related = $product->relatedProducts()->with('related')->orderBy('position')->get();

        return RelatedProductResource::collection($related);
    }

    public function store(StoreRelatedProductRequest $request, Product $product): RelatedProductResource
    {
        $data = $request->validated();

        if ($data['related_product_id'] === $product->id) {
            throw ValidationException::withMessages([
                'related_product_id' => __('A product cannot be related to itself.'),
            ]);
        }

        $related = RelatedProduct::updateOrCreate(
            [
                'product_id' => $product->id,
                'related_product_id' => $data['related_product_id'],
                'relation_type' => $data['relation_type'] ?? 'related',
            ],
            [
                'position' => $data['position'] ?? ($product->relatedProducts()->max('position') + 1),
            ],
        );

        return RelatedProductResource::make($related->load('related'));
    }

    public function show(Product $product, RelatedProduct $relatedProduct): RelatedProductResource
    {
        $this->ensureRelationBelongsToProduct($product, $relatedProduct);

        return RelatedProductResource::make($relatedProduct->load('related'));
    }

    public function update(UpdateRelatedProductRequest $request, Product $product, RelatedProduct $relatedProduct): RelatedProductResource
    {
        $this->ensureRelationBelongsToProduct($product, $relatedProduct);

        $data = $request->validated();

        $relatedProduct->update($data);

        return RelatedProductResource::make($relatedProduct->fresh('related'));
    }

    public function destroy(Product $product, RelatedProduct $relatedProduct): Response
    {
        $this->ensureRelationBelongsToProduct($product, $relatedProduct);

        $relatedProduct->delete();

        return response()->noContent();
    }

    protected function ensureRelationBelongsToProduct(Product $product, RelatedProduct $relatedProduct): void
    {
        if ($relatedProduct->product_id !== $product->id) {
            abort(404);
        }
    }
}
