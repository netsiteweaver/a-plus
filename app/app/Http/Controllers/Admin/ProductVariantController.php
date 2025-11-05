<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Http\Resources\Admin\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ProductVariantController extends AdminController
{
    public function index(Product $product): AnonymousResourceCollection
    {
        $variants = $product->variants()->with('optionValues')->orderByDesc('is_default')->orderBy('id')->get();

        return ProductVariantResource::collection($variants);
    }

    public function store(StoreProductVariantRequest $request, Product $product): ProductVariantResource
    {
        $data = $request->validated();

        $variant = $product->variants()->create([
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'cost' => $data['cost'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'inventory_sku' => $data['inventory_sku'] ?? null,
            'inventory_policy' => $data['inventory_policy'] ?? 'deny',
            'inventory_quantity' => $data['inventory_quantity'] ?? 0,
            'track_inventory' => $data['track_inventory'] ?? true,
            'weight' => $data['weight'] ?? null,
            'weight_unit' => $data['weight_unit'] ?? null,
            'length' => $data['length'] ?? null,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'dimension_unit' => $data['dimension_unit'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'requires_shipping' => $data['requires_shipping'] ?? true,
            'requires_serial' => $data['requires_serial'] ?? false,
            'published_at' => $data['published_at'] ?? null,
            'data' => $data['data'] ?? null,
        ]);

        $this->syncOptionValues($product, $variant, $data['option_value_ids'] ?? []);

        if (($data['is_default'] ?? false) || ! $product->default_variant_id) {
            $this->setDefaultVariant($product, $variant);
        }

        return ProductVariantResource::make($variant->fresh('optionValues'));
    }

    public function show(Product $product, ProductVariant $variant): ProductVariantResource
    {
        $this->ensureVariantBelongsToProduct($product, $variant);

        return ProductVariantResource::make($variant->load('optionValues'));
    }

    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant): ProductVariantResource
    {
        $this->ensureVariantBelongsToProduct($product, $variant);

        $data = $request->validated();

        $updates = collect($data)
            ->except(['option_value_ids', 'is_default'])
            ->toArray();

        if (! empty($updates)) {
            $variant->update($updates);
        }

        if (array_key_exists('option_value_ids', $data)) {
            $this->syncOptionValues($product, $variant, $data['option_value_ids'] ?? []);
        }

        if (array_key_exists('is_default', $data) && $data['is_default']) {
            $this->setDefaultVariant($product, $variant);
        }

        return ProductVariantResource::make($variant->fresh('optionValues'));
    }

    public function destroy(Product $product, ProductVariant $variant): Response
    {
        $this->ensureVariantBelongsToProduct($product, $variant);

        $wasDefault = $variant->is_default;

        $variant->delete();

        if ($wasDefault) {
            $replacement = $product->variants()
                ->where('id', '!=', $variant->id)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->first();

            if ($replacement) {
                $this->setDefaultVariant($product, $replacement);
            } else {
                $product->update(['default_variant_id' => null]);
            }
        }

        return response()->noContent();
    }

    protected function ensureVariantBelongsToProduct(Product $product, ProductVariant $variant): void
    {
        if ($variant->product_id !== $product->id) {
            abort(404);
        }
    }

    protected function syncOptionValues(Product $product, ProductVariant $variant, array $optionValueIds): void
    {
        if (empty($optionValueIds)) {
            $variant->optionValues()->detach();

            return;
        }

        $validIds = ProductOptionValue::query()
            ->whereIn('id', $optionValueIds)
            ->whereHas('option', fn ($query) => $query->where('product_id', $product->id))
            ->pluck('id');

        $invalid = collect($optionValueIds)->diff($validIds);
        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'option_value_ids' => __('One or more option values are invalid for this product.'),
            ]);
        }

        $variant->optionValues()->sync($validIds->all());
    }

    protected function setDefaultVariant(Product $product, ProductVariant $variant): void
    {
        $product->variants()->update(['is_default' => false]);

        $variant->update(['is_default' => true]);

        $product->update(['default_variant_id' => $variant->id]);
    }
}
