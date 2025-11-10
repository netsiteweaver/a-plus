<?php

namespace App\Services\WooCommerce;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductMedia;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WooCommerceProductImporter
{
    public function __construct(
        protected WooCommerceClient $client
    ) {
    }

    public function import(array $options = [], ?callable $progress = null): array
    {
        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => [],
        ];

        $productIds = collect(Arr::get($options, 'product_ids', []))
            ->filter()
            ->unique()
            ->values();

        if ($productIds->isNotEmpty()) {
            foreach ($productIds as $remoteId) {
                try {
                    $remoteProduct = $this->client->getProduct((int) $remoteId);
                } catch (Throwable $exception) {
                    $summary['failed'][] = [
                        'remote_id' => $remoteId,
                        'message' => $exception->getMessage(),
                    ];
                    Log::error('WooCommerce product fetch failed.', [
                        'remote_id' => $remoteId,
                        'exception' => $exception,
                    ]);
                    $this->emitProgress($progress, 'failed', ['remote_id' => $remoteId]);
                    continue;
                }

                $summary = $this->importRemoteProduct($remoteProduct, $summary, $progress);
            }

            return $summary;
        }

        $statuses = Arr::get($options, 'statuses', $this->client->defaultStatuses());
        $perPage = (int) ($options['per_page'] ?? $this->client->defaultPerPage());
        $limit = Arr::get($options, 'limit');
        $page = (int) ($options['page'] ?? 1);
        $processed = 0;

        do {
            try {
                $remoteProducts = $this->client->listProducts($page, [
                    'per_page' => $perPage,
                    'statuses' => $statuses,
                ]);
            } catch (Throwable $exception) {
                Log::error('WooCommerce product list fetch failed.', [
                    'page' => $page,
                    'exception' => $exception,
                ]);
                $summary['failed'][] = [
                    'page' => $page,
                    'message' => $exception->getMessage(),
                ];
                break;
            }

            if (empty($remoteProducts)) {
                break;
            }

            foreach ($remoteProducts as $remoteProduct) {
                if ($limit && $processed >= $limit) {
                    break 2;
                }

                $summary = $this->importRemoteProduct($remoteProduct, $summary, $progress);
                $processed++;
            }

            $page++;
        } while (count($remoteProducts) === $perPage);

        return $summary;
    }

    protected function importRemoteProduct(array $remoteProduct, array $summary, ?callable $progress = null): array
    {
        $remoteId = (int) Arr::get($remoteProduct, 'id');

        if (! $remoteId) {
            $summary['skipped']++;
            Log::warning('Skipping WooCommerce product without an ID.', ['product' => $remoteProduct]);
            $this->emitProgress($progress, 'skipped', ['reason' => 'missing_id']);

            return $summary;
        }

        try {
            [$product, $action] = DB::transaction(function () use ($remoteProduct) {
                return $this->synchroniseProduct($remoteProduct);
            }, attempts: 2);

            $summary[$action]++;

            $this->emitProgress($progress, $action, [
                'product_id' => $product->id,
                'remote_id' => $remoteId,
            ]);
        } catch (Throwable $exception) {
            $summary['failed'][] = [
                'remote_id' => $remoteId,
                'message' => $exception->getMessage(),
            ];
            Log::error('WooCommerce product import failed.', [
                'remote_id' => $remoteId,
                'exception' => $exception,
            ]);
            $this->emitProgress($progress, 'failed', [
                'remote_id' => $remoteId,
                'message' => $exception->getMessage(),
            ]);
        }

        return $summary;
    }

    /**
     * @return array{0: \App\Models\Product, 1: string}
     */
    protected function synchroniseProduct(array $remoteProduct): array
    {
        $remoteId = (int) Arr::get($remoteProduct, 'id');

        $existing = $this->findExistingProduct($remoteId);

        $isNew = ! $existing;
        $product = $existing ?? new Product();

        $slugSource = Arr::get($remoteProduct, 'slug') ?: Arr::get($remoteProduct, 'name') ?: 'product-'.$remoteId;
        $productSlug = $this->uniqueSlug($slugSource, 'products', $product->id);

        if (! $product->exists) {
            $product->uuid = (string) Str::uuid();
        }

        $status = $this->mapProductStatus(Arr::get($remoteProduct, 'status'));
        $publishedAt = $status === 'published'
            ? $this->parseDate(Arr::get($remoteProduct, 'date_created'))
            : null;

        $product->fill([
            'slug' => $productSlug,
            'type' => $this->mapProductType(Arr::get($remoteProduct, 'type')),
            'name' => trim((string) Arr::get($remoteProduct, 'name')),
            'sku' => Arr::get($remoteProduct, 'sku') ?: null,
            'excerpt' => $this->cleanExcerpt(Arr::get($remoteProduct, 'short_description')),
            'description' => Arr::get($remoteProduct, 'description') ?: null,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);

        $product->data = $this->buildProductData($product, $remoteProduct);

        $product->save();

        $this->resetProductAssociations($product);

        $this->syncCategories($product, Arr::get($remoteProduct, 'categories', []));
        $this->syncProductAttributes($product, Arr::get($remoteProduct, 'attributes', []));

        $this->syncProductMedia($product, Arr::get($remoteProduct, 'images', []));

        $variations = $this->resolveVariationPayload($remoteProduct);

        $variants = $this->syncProductVariants($product, $remoteProduct, $variations);

        $defaultVariant = $variants->firstWhere('is_default', true) ?? $variants->first();
        if ($defaultVariant) {
            $product->default_variant_id = $defaultVariant->id;
            $product->save();
        }

        return [$product, $isNew ? 'created' : 'updated'];
    }

    protected function findExistingProduct(int $remoteId): ?Product
    {
        return Product::query()
            ->where('data->woocommerce->id', $remoteId)
            ->first();
    }

    protected function uniqueSlug(string $source, string $table, ?int $ignoreId = null): string
    {
        $slug = Str::slug($source);

        if ($slug === '') {
            $slug = Str::slug(Str::limit($source, 60, '')) ?: Str::lower(Str::random(10));
        }

        $candidate = $slug;
        $counter = 2;

        while ($this->slugExists($table, $candidate, $ignoreId)) {
            $candidate = "{$slug}-{$counter}";
            $counter++;
        }

        return $candidate;
    }

    protected function slugExists(string $table, string $slug, ?int $ignoreId): bool
    {
        $query = DB::table($table)->where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    protected function mapProductStatus(?string $status): string
    {
        return match ($status) {
            'publish', 'published' => 'published',
            'private', 'trash' => 'archived',
            default => 'draft',
        };
    }

    protected function mapVariantStatus(?string $status): string
    {
        return match ($status) {
            'publish', 'published' => 'published',
            'private', 'trash' => 'archived',
            default => 'draft',
        };
    }

    protected function mapProductType(?string $type): string
    {
        return match ($type) {
            'variable' => 'variable',
            'grouped' => 'grouped',
            'external' => 'external',
            default => 'standard',
        };
    }

    protected function cleanExcerpt(?string $excerpt): ?string
    {
        if (! $excerpt) {
            return null;
        }

        $clean = trim(strip_tags($excerpt));

        return $clean !== '' ? $clean : null;
    }

    protected function parseDate(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }

    protected function buildProductData(Product $product, array $remote): array
    {
        $existingData = is_array($product->data) ? $product->data : [];

        $existingData['source'] = 'woocommerce';
        $existingData['woocommerce'] = array_merge(
            Arr::get($existingData, 'woocommerce', []),
            [
                'id' => Arr::get($remote, 'id'),
                'permalink' => Arr::get($remote, 'permalink'),
                'type' => Arr::get($remote, 'type'),
                'status' => Arr::get($remote, 'status'),
                'last_synced_at' => now()->toIso8601String(),
            ]
        );

        return $existingData;
    }

    protected function resetProductAssociations(Product $product): void
    {
        $product->media()->withTrashed()->get()->each(function (ProductMedia $media) {
            $media->forceDelete();
        });

        $product->options()->withTrashed()->get()->each(function (ProductOption $option) {
            $option->forceDelete();
        });

        $product->variants()->withTrashed()->get()->each(function (ProductVariant $variant) {
            $variant->forceDelete();
        });

        ProductAttributeValue::query()
            ->where('product_id', $product->id)
            ->delete();

        $product->default_variant_id = null;
        $product->save();
    }

    protected function syncCategories(Product $product, array $remoteCategories): void
    {
        if (empty($remoteCategories)) {
            $product->categories()->detach();

            return;
        }

        $syncPayload = [];

        foreach ($remoteCategories as $index => $remoteCategory) {
            $category = $this->upsertCategory($remoteCategory);

            if (! $category) {
                continue;
            }

            $syncPayload[$category->id] = [
                'is_primary' => $index === 0 ? 1 : 0,
                'position' => $index + 1,
            ];
        }

        $product->categories()->sync($syncPayload);
    }

    protected function upsertCategory(array $remoteCategory): ?Category
    {
        $remoteId = Arr::get($remoteCategory, 'id');
        if (! $remoteId) {
            return null;
        }

        $category = Category::query()
            ->where('data->woocommerce->id', $remoteId)
            ->first();

        $name = trim((string) Arr::get($remoteCategory, 'name'));
        $slugSource = Arr::get($remoteCategory, 'slug') ?: $name ?: 'category-'.$remoteId;

        if (! $category) {
            $category = Category::query()
                ->where('slug', $slugSource)
                ->first();
        }

        if (! $category) {
            $category = new Category();
        }

        $category->name = $name ?: 'Category '.$remoteId;
        $category->slug = $this->uniqueSlug($slugSource, 'categories', $category->id);
        $category->status = 'published';
        $category->is_visible = true;
        $category->position = $category->position ?? 0;
        $category->type = 'catalog';
        $category->description = Arr::get($remoteCategory, 'description') ?: $category->description;

        $data = is_array($category->data) ? $category->data : [];
        $data['source'] = 'woocommerce';
        $data['woocommerce'] = array_merge(
            Arr::get($data, 'woocommerce', []),
            [
                'id' => $remoteId,
                'slug' => Arr::get($remoteCategory, 'slug'),
            ]
        );

        $category->data = $data;
        $category->save();

        return $category;
    }

    protected function syncProductAttributes(Product $product, array $remoteAttributes): void
    {
        if (empty($remoteAttributes)) {
            ProductAttributeValue::query()
                ->where('product_id', $product->id)
                ->delete();

            return;
        }

        foreach ($remoteAttributes as $attribute) {
            if (Arr::get($attribute, 'variation')) {
                continue;
            }

            $attributeModel = $this->upsertAttribute($attribute);

            if (! $attributeModel) {
                continue;
            }

            $options = Arr::get($attribute, 'options', []);

            foreach ($options as $option) {
                $option = trim((string) $option);

                if ($option === '') {
                    continue;
                }

                $attributeValue = $this->upsertAttributeValue($attributeModel, $option);

                ProductAttributeValue::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'attribute_id' => $attributeModel->id,
                        'attribute_value_id' => $attributeValue?->id,
                    ],
                    [
                        'value_text' => $option,
                        'value_number' => $this->extractNumericValue($option),
                    ]
                );
            }
        }
    }

    protected function upsertAttribute(array $remoteAttribute): ?Attribute
    {
        $name = trim((string) Arr::get($remoteAttribute, 'name'));

        if ($name === '') {
            return null;
        }

        $codeSource = Arr::get($remoteAttribute, 'slug') ?: $name;
        $code = Str::slug($codeSource);

        if ($code === '') {
            $code = 'attribute-'.Str::lower(Str::random(8));
        }

        $attribute = Attribute::withTrashed()
            ->where('code', $code)
            ->first();

        if (! $attribute) {
            $attribute = Attribute::create([
                'code' => $code,
                'name' => $name,
                'type' => 'text',
                'is_filterable' => true,
                'is_required' => false,
                'data' => [
                    'source' => 'woocommerce',
                    'woocommerce' => [
                        'id' => Arr::get($remoteAttribute, 'id'),
                        'slug' => Arr::get($remoteAttribute, 'slug'),
                    ],
                ],
            ]);
        } else {
            $attribute->restore();
            $attribute->update([
                'name' => $name,
                'data' => array_merge($attribute->data ?? [], [
                    'source' => 'woocommerce',
                    'woocommerce' => [
                        'id' => Arr::get($remoteAttribute, 'id'),
                        'slug' => Arr::get($remoteAttribute, 'slug'),
                    ],
                ]),
            ]);
        }

        return $attribute;
    }

    protected function upsertAttributeValue(Attribute $attribute, string $value): ?AttributeValue
    {
        return AttributeValue::withTrashed()->firstOrCreate(
            [
                'attribute_id' => $attribute->id,
                'value' => $value,
            ],
            [
                'display_value' => $value,
                'numeric_value' => $this->extractNumericValue($value),
                'data' => null,
            ]
        );
    }

    protected function extractNumericValue(string $value): ?float
    {
        $normalized = preg_replace('/[^0-9\.\-]/', '', $value ?? '');

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    protected function syncProductMedia(Product $product, array $remoteImages): void
    {
        $position = 1;

        foreach ($remoteImages as $index => $image) {
            $src = Arr::get($image, 'src');

            if (! $src) {
                continue;
            }

            ProductMedia::create([
                'product_id' => $product->id,
                'type' => 'image',
                'disk' => null,
                'path' => $src,
                'is_primary' => $index === 0,
                'position' => $position++,
                'alt_text' => Arr::get($image, 'alt'),
                'caption' => Arr::get($image, 'name'),
                'data' => [
                    'source' => 'woocommerce',
                    'woocommerce' => [
                        'id' => Arr::get($image, 'id'),
                    ],
                ],
            ]);
        }
    }

    protected function resolveVariationPayload(array $remoteProduct): array
    {
        $type = Arr::get($remoteProduct, 'type');

        if ($type === 'variable') {
            return $this->fetchAllVariations((int) Arr::get($remoteProduct, 'id'));
        }

        return [
            array_merge($remoteProduct, [
                '_is_simple_product' => true,
                'parent_id' => Arr::get($remoteProduct, 'id'),
                'attributes' => Arr::get($remoteProduct, 'attributes', []),
            ]),
        ];
    }

    protected function fetchAllVariations(int $remoteProductId): array
    {
        $page = 1;
        $perPage = min(100, $this->client->defaultPerPage());
        $variations = [];

        do {
            try {
                $batch = $this->client->listProductVariations($remoteProductId, $page, [
                    'per_page' => $perPage,
                ]);
            } catch (Throwable $exception) {
                Log::error('Failed to fetch WooCommerce product variations.', [
                    'remote_product_id' => $remoteProductId,
                    'page' => $page,
                    'exception' => $exception,
                ]);
                break;
            }

            if (empty($batch)) {
                break;
            }

            $variations = array_merge($variations, $batch);
            $page++;
        } while (count($batch) === $perPage);

        return $variations;
    }

    protected function syncProductVariants(Product $product, array $remoteProduct, array $variations): EloquentCollection
    {
        $optionsMap = $this->buildProductOptions($product, Arr::get($remoteProduct, 'attributes', []));
        $defaultAttributes = $this->normalizeDefaultAttributes(Arr::get($remoteProduct, 'default_attributes', []));
        $currency = config('woocommerce.default_currency', 'USD');

        $variants = new EloquentCollection();

        foreach ($variations as $index => $variation) {
            $variant = $this->createVariant(
                $product,
                $variation,
                $optionsMap,
                $defaultAttributes,
                $currency,
                $index
            );

            $variants->push($variant);
        }

        return $variants;
    }

    /**
     * @return array<string, array{option: \App\Models\ProductOption, values: array<string, \App\Models\ProductOptionValue>}>
     */
    protected function buildProductOptions(Product $product, array $remoteAttributes): array
    {
        $options = [];

        foreach ($remoteAttributes as $index => $attribute) {
            if (! Arr::get($attribute, 'variation')) {
                continue;
            }

            $slug = $this->attributeKey($attribute);
            $name = trim((string) Arr::get($attribute, 'name')) ?: Str::title(str_replace('-', ' ', $slug));

            $option = ProductOption::create([
                'product_id' => $product->id,
                'code' => $slug,
                'name' => $name,
                'input_type' => 'select',
                'position' => $index + 1,
                'is_required' => true,
                'data' => [
                    'source' => 'woocommerce',
                    'woocommerce' => [
                        'id' => Arr::get($attribute, 'id'),
                    ],
                ],
            ]);

            $valueMap = [];
            foreach (Arr::get($attribute, 'options', []) as $valueIndex => $value) {
                $normalizedValue = trim((string) $value);

                if ($normalizedValue === '') {
                    continue;
                }

                $valueMap[$normalizedValue] = ProductOptionValue::create([
                    'product_option_id' => $option->id,
                    'value' => $normalizedValue,
                    'display_value' => $normalizedValue,
                    'position' => $valueIndex + 1,
                    'data' => [
                        'source' => 'woocommerce',
                    ],
                ]);
            }

            $options[$slug] = [
                'option' => $option,
                'values' => $valueMap,
            ];
        }

        return $options;
    }

    protected function attributeKey(array $attribute): string
    {
        $slug = Arr::get($attribute, 'slug');

        if ($slug) {
            return Str::slug($slug);
        }

        $name = Arr::get($attribute, 'name');

        if ($name) {
            return Str::slug($name);
        }

        return 'option-'.Str::lower(Str::random(6));
    }

    protected function normalizeDefaultAttributes(array $defaultAttributes): array
    {
        $normalized = [];

        foreach ($defaultAttributes as $attribute) {
            $key = $this->attributeKey($attribute);
            $normalized[$key] = Str::lower(trim((string) Arr::get($attribute, 'option')));
        }

        return $normalized;
    }

    protected function createVariant(
        Product $product,
        array $variation,
        array &$optionsMap,
        array $defaultAttributes,
        string $currency,
        int $index
    ): ProductVariant {
        $remoteVariantId = (int) Arr::get($variation, 'id');
        $sku = $this->resolveVariantSku($product, $variation);

        $variant = new ProductVariant();
        $variant->product_id = $product->id;
        $variant->sku = $sku;
        $variant->barcode = Arr::get($variation, 'barcode');
        $variant->status = $this->mapVariantStatus(Arr::get($variation, 'status', Arr::get($variation, 'stock_status')));
        $variant->price = $this->resolveVariantPrice($variation);
        $variant->compare_at_price = $this->resolveVariantComparePrice($variation);
        $variant->cost = null;
        $variant->currency = $currency;
        $variant->inventory_sku = Arr::get($variation, 'sku') ?: null;
        $variant->inventory_policy = $this->mapInventoryPolicy(Arr::get($variation, 'backorders'));
        $variant->inventory_quantity = $this->resolveInventoryQuantity($variation);
        $variant->track_inventory = (bool) Arr::get($variation, 'manage_stock', false);
        $variant->weight = $this->toNullableFloat(Arr::get($variation, 'weight'));
        $variant->weight_unit = Arr::get($variation, 'weight') ? Arr::get($variation, 'weight_unit') : null;
        $variant->length = $this->toNullableFloat(Arr::get($variation, 'dimensions.length'));
        $variant->width = $this->toNullableFloat(Arr::get($variation, 'dimensions.width'));
        $variant->height = $this->toNullableFloat(Arr::get($variation, 'dimensions.height'));
        $variant->dimension_unit = Arr::get($variation, 'dimensions.length') ? Arr::get($variation, 'dimensions.unit') : null;
        $variant->is_default = $this->determineVariantDefault($variation, $defaultAttributes, $index === 0);
        $variant->requires_shipping = ! (bool) Arr::get($variation, 'virtual', false);
        $variant->requires_serial = false;
        $variant->published_at = $this->parseDate(Arr::get($variation, 'date_created', Arr::get($variation, 'date_created_gmt')));
        $variant->data = [
            'source' => 'woocommerce',
            'woocommerce' => [
                'product_id' => Arr::get($variation, 'parent_id'),
                'id' => $remoteVariantId ?: Arr::get($variation, 'id'),
                'permalink' => Arr::get($variation, 'permalink', Arr::get($variation, 'url')),
                'raw' => Arr::only($variation, ['stock_quantity', 'stock_status', 'menu_order']),
            ],
        ];

        $variant->save();

        $this->syncVariantOptionValues($variant, $variation, $optionsMap);

        $this->syncVariantMedia($product, $variant, $variation);

        return $variant;
    }

    protected function resolveVariantSku(Product $product, array $variation): string
    {
        $sku = Arr::get($variation, 'sku');

        if ($sku) {
            return $sku;
        }

        $remoteId = Arr::get($variation, 'id');

        if ($remoteId) {
            return 'wc-'.$remoteId;
        }

        if ($product->sku) {
            return $product->sku.'-'.Str::lower(Str::random(4));
        }

        return 'wc-variant-'.Str::lower(Str::random(8));
    }

    protected function resolveVariantPrice(array $variation): float
    {
        $price = Arr::get($variation, 'price');

        if ($price === '' || $price === null) {
            $price = Arr::get($variation, 'regular_price');
        }

        if ($price === '' || $price === null) {
            $price = Arr::get($variation, 'sale_price');
        }

        return (float) ($price ?: 0);
    }

    protected function resolveVariantComparePrice(array $variation): ?float
    {
        $regular = Arr::get($variation, 'regular_price');
        $sale = Arr::get($variation, 'sale_price');

        if ($sale && $regular && (float) $sale < (float) $regular) {
            return (float) $regular;
        }

        return null;
    }

    protected function mapInventoryPolicy(?string $backorders): string
    {
        return match ($backorders) {
            'notify', 'notify_only' => 'continue',
            'yes' => 'continue',
            default => 'deny',
        };
    }

    protected function resolveInventoryQuantity(array $variation): int
    {
        if ((bool) Arr::get($variation, 'manage_stock')) {
            return (int) Arr::get($variation, 'stock_quantity', 0);
        }

        return 0;
    }

    protected function determineVariantDefault(array $variation, array $defaultAttributes, bool $isFirst): bool
    {
        if (! empty($defaultAttributes)) {
            $attributes = Arr::get($variation, 'attributes', []);
            $normalized = [];

            foreach ($attributes as $attribute) {
                $key = $this->attributeKey($attribute);
                $normalized[$key] = Str::lower(trim((string) Arr::get($attribute, 'option')));
            }

            foreach ($defaultAttributes as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                if (! isset($normalized[$key]) || $normalized[$key] !== $value) {
                    return false;
                }
            }

            return true;
        }

        return $isFirst;
    }

    protected function syncVariantOptionValues(ProductVariant $variant, array $variation, array &$optionsMap): void
    {
        $attributeValues = [];

        foreach (Arr::get($variation, 'attributes', []) as $attribute) {
            $key = $this->attributeKey($attribute);
            $optionValue = trim((string) Arr::get($attribute, 'option'));

            if ($optionValue === '') {
                continue;
            }

            if (! isset($optionsMap[$key])) {
                $option = ProductOption::create([
                    'product_id' => $variant->product_id,
                    'code' => $key,
                    'name' => Str::title(str_replace('-', ' ', $key)),
                    'input_type' => 'select',
                    'position' => count($optionsMap) + 1,
                    'is_required' => true,
                    'data' => [
                        'source' => 'woocommerce',
                    ],
                ]);

                $optionsMap[$key] = [
                    'option' => $option,
                    'values' => [],
                ];
            }

            if (! isset($optionsMap[$key]['values'][$optionValue])) {
                $optionsMap[$key]['values'][$optionValue] = ProductOptionValue::create([
                    'product_option_id' => $optionsMap[$key]['option']->id,
                    'value' => $optionValue,
                    'display_value' => $optionValue,
                    'position' => count($optionsMap[$key]['values']) + 1,
                    'data' => [
                        'source' => 'woocommerce',
                    ],
                ]);
            }

            $attributeValues[] = $optionsMap[$key]['values'][$optionValue]->id;
        }

        if (! empty($attributeValues)) {
            $variant->optionValues()->sync($attributeValues);
        }
    }

    protected function syncVariantMedia(Product $product, ProductVariant $variant, array $variation): void
    {
        $image = Arr::get($variation, 'image');

        if (! $image || ! Arr::get($image, 'src')) {
            return;
        }

        $position = ProductMedia::where('product_id', $product->id)->max('position');
        $position = $position ? $position + 1 : 1;

        ProductMedia::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'type' => 'image',
            'disk' => null,
            'path' => Arr::get($image, 'src'),
            'is_primary' => false,
            'position' => $position,
            'alt_text' => Arr::get($image, 'alt'),
            'caption' => Arr::get($image, 'name'),
            'data' => [
                'source' => 'woocommerce',
                'woocommerce' => [
                    'id' => Arr::get($image, 'id'),
                    'variant_id' => Arr::get($variation, 'id'),
                ],
            ],
        ]);
    }

    protected function toNullableFloat(mixed $value): ?float
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^0-9\.\-]/', '', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    protected function emitProgress(?callable $callback, string $event, array $payload = []): void
    {
        if (! $callback) {
            return;
        }

        $callback($event, $payload);
    }
}
