<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductMediaRequest;
use App\Http\Requests\Admin\UpdateProductMediaRequest;
use App\Http\Resources\Admin\ProductMediaResource;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ProductMediaController extends AdminController
{
    public function index(Product $product): AnonymousResourceCollection
    {
        $media = $product->media()->orderBy('position')->get();

        return ProductMediaResource::collection($media);
    }

    public function store(StoreProductMediaRequest $request, Product $product): ProductMediaResource
    {
        $data = $request->validated();

        $this->ensureVariantBelongsToProduct($product, $data['product_variant_id'] ?? null);

        $media = $product->media()->create([
            'type' => $data['type'] ?? 'image',
            'disk' => $data['disk'] ?? null,
            'path' => $data['path'],
            'url' => $data['url'] ?? null,
            'product_variant_id' => $data['product_variant_id'] ?? null,
            'is_primary' => $data['is_primary'] ?? false,
            'position' => $data['position'] ?? ($product->media()->max('position') + 1),
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
            'data' => $data['data'] ?? null,
        ]);

        if ($media->is_primary) {
            $this->unsetOtherPrimaryMedia($product, $media->id);
        }

        return ProductMediaResource::make($media);
    }

    public function show(Product $product, ProductMedia $medium): ProductMediaResource
    {
        $this->ensureMediaBelongsToProduct($product, $medium);

        return ProductMediaResource::make($medium);
    }

    public function update(UpdateProductMediaRequest $request, Product $product, ProductMedia $medium): ProductMediaResource
    {
        $this->ensureMediaBelongsToProduct($product, $medium);

        $data = $request->validated();

        if (array_key_exists('product_variant_id', $data)) {
            $this->ensureVariantBelongsToProduct($product, $data['product_variant_id']);
        }

        $medium->update($data);

        if (array_key_exists('is_primary', $data) && $data['is_primary']) {
            $this->unsetOtherPrimaryMedia($product, $medium->id);
        }

        return ProductMediaResource::make($medium->fresh());
    }

    public function destroy(Product $product, ProductMedia $medium): Response
    {
        $this->ensureMediaBelongsToProduct($product, $medium);

        $medium->delete();

        return response()->noContent();
    }

    protected function ensureMediaBelongsToProduct(Product $product, ProductMedia $media): void
    {
        if ($media->product_id !== $product->id) {
            abort(404);
        }
    }

    protected function ensureVariantBelongsToProduct(Product $product, ?int $variantId): void
    {
        if (! $variantId) {
            return;
        }

        if (! ProductVariant::query()->where('product_id', $product->id)->where('id', $variantId)->exists()) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('The selected variant is invalid for this product.'),
            ]);
        }
    }

    protected function unsetOtherPrimaryMedia(Product $product, int $mediaId): void
    {
        $product->media()
            ->where('id', '!=', $mediaId)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }
}
