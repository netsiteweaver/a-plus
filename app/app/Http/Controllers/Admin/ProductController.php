<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends AdminController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'include' => ['nullable', 'array'],
            'include.*' => ['string', 'in:brand,categories,variants,options,media,attributeValues,relatedProducts'],
        ]);

        $query = Product::query()
            ->with('brand')
            ->withCount(['variants', 'media'])
            ->orderByDesc('updated_at');

        if ($includes = $request->input('include', [])) {
            $query->with($includes);
        }

        $query->when($request->filled('search'), function ($q) use ($request) {
            $keyword = $request->input('search');
            $q->where(function ($inner) use ($keyword) {
                $inner->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%");
            });
        });

        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));
        $query->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->integer('brand_id')));
        $query->when($request->filled('category_id'), function ($q) use ($request) {
            $q->whereHas('categories', fn ($relation) => $relation->where('categories.id', $request->integer('category_id')));
        });

        $products = $query->paginate($request->integer('per_page', 15));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $data = $request->validated();

        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => $data['slug'],
            'type' => $data['type'] ?? 'standard',
            'brand_id' => $data['brand_id'] ?? null,
            'sku' => $data['sku'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'description' => $data['description'] ?? null,
            'specifications' => $data['specifications'] ?? null,
            'data' => $data['data'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'published_at' => $data['published_at'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        $this->syncCategories($product, $data['category_ids'] ?? [], $data['primary_category_id'] ?? null);

        $product->load(['brand', 'categories']);

        return ProductResource::make($product);
    }

    public function show(Request $request, Product $product): ProductResource
    {
        $request->validate([
            'include' => ['nullable', 'array'],
            'include.*' => ['string', 'in:brand,categories,variants,options,media,attributeValues,relatedProducts'],
        ]);

        $includes = $request->input('include', []);
        if ($includes) {
            $product->load($includes);
        }

        $product->loadMissing(['brand', 'categories']);

        return ProductResource::make($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $data = $request->validated();

        $updates = collect($data)
            ->except(['category_ids', 'primary_category_id', 'default_variant_id'])
            ->toArray();

        if (! empty($updates)) {
            $updates['updated_by'] = $request->user()?->id;
            $product->update($updates);
        }

        if ($request->has('category_ids') || $request->has('primary_category_id')) {
            $this->syncCategories($product, $data['category_ids'] ?? [], $data['primary_category_id'] ?? null);
        }

        if (array_key_exists('default_variant_id', $data)) {
            $defaultVariantId = $data['default_variant_id'];

            if ($defaultVariantId && ! $product->variants()->whereKey($defaultVariantId)->exists()) {
                throw ValidationException::withMessages([
                    'default_variant_id' => __('The selected default variant does not belong to this product.'),
                ]);
            }

            $product->default_variant_id = $defaultVariantId;
            $product->save();
        }

        $product->load(['brand', 'categories']);

        return ProductResource::make($product);
    }

    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }

    protected function syncCategories(Product $product, array $categoryIds, ?int $primaryCategoryId): void
    {
        if (empty($categoryIds)) {
            $product->categories()->detach();

            return;
        }

        $primaryCategoryId = $primaryCategoryId ?: reset($categoryIds);

        $syncData = collect($categoryIds)
            ->unique()
            ->mapWithKeys(function ($id, $index) use ($primaryCategoryId) {
                return [
                    $id => [
                        'is_primary' => (int) ($id == $primaryCategoryId),
                        'position' => $index + 1,
                    ],
                ];
            })
            ->all();

        $product->categories()->sync($syncData);
    }
}
