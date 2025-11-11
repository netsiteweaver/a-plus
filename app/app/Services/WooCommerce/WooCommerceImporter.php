<?php

namespace App\Services\WooCommerce;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class WooCommerceImporter
{
    public const SOURCE = 'woocommerce';

    protected WooCommerceClient $client;
    protected string $currency;
    protected array $stats = [];
    protected array $categoryMap = [];
    protected array $optionCache = [];
    protected array $productMediaCache = [];
    protected ?OutputInterface $output = null;
    protected ?\Closure $progressCallback = null;

    public function __construct(WooCommerceClient $client)
    {
        $this->client = $client;
        $this->currency = strtoupper((string) (config('woocommerce.default_currency') ?? 'USD'));
        $this->hydrateCategoryCache();
        $this->resetStats();
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, array<string, int>>
     */
    public function import(array $options = [], ?OutputInterface $output = null, ?\Closure $progressCallback = null): array
    {
        $this->output = $output;
        $this->progressCallback = $progressCallback;
        $this->resetStats();
        $this->hydrateCategoryCache();

        if (($options['sync_categories'] ?? true) !== false) {
            $this->log('<info>Synchronising WooCommerce categories…</info>');
            $this->syncCategories();
        }

        $productQuery = $this->buildProductQuery($options);
        $this->log('<info>Fetching products from WooCommerce…</info>', OutputInterface::VERBOSITY_VERBOSE);

        foreach ($this->client->getProducts($productQuery) as $remoteProduct) {
            if (! is_array($remoteProduct) || empty($remoteProduct['id'])) {
                continue;
            }

            try {
                DB::transaction(fn () => $this->importProduct($remoteProduct), 3);
            } catch (Throwable $exception) {
                $this->stats['products']['skipped']++;
                $message = sprintf(
                    'Failed to import product #%s: %s',
                    $remoteProduct['id'],
                    $exception->getMessage()
                );
                $this->log("<error>{$message}</error>");
            }
        }

        return $this->stats;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function buildProductQuery(array $options): array
    {
        $query = [];

        if (! empty($options['per_page'])) {
            $query['per_page'] = max(1, min((int) $options['per_page'], 100));
        }

        if (! empty($options['remote_ids'])) {
            $ids = array_unique(array_filter(
                array_map('intval', (array) $options['remote_ids']),
                fn ($id) => $id > 0
            ));

            if (! empty($ids)) {
                $query['include'] = implode(',', $ids);
                $query['per_page'] = max($query['per_page'] ?? $this->client->defaultPerPage(), count($ids));
            }
        }

        if (! empty($options['status'])) {
            $query['status'] = implode(',', array_map('trim', explode(',', (string) $options['status'])));
        }

        if (! empty($options['since'])) {
            $query['modified_after'] = $this->formatDateTime($options['since']);
        }

        return $query;
    }

    protected function importProduct(array $remoteProduct): void
    {
        $product = $this->upsertProduct($remoteProduct);

        $this->syncProductCategories($product, Arr::get($remoteProduct, 'categories', []));

        $optionMap = $this->syncProductOptions($product, Arr::get($remoteProduct, 'attributes', []));

        $downloadingImages = config('woocommerce.download_images', true);
        $imageCount = count(Arr::get($remoteProduct, 'images', []));
        
        if ($downloadingImages && $imageCount > 0) {
            $this->emitProgress('downloading_images', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'image_count' => $imageCount,
            ]);
        }

        $this->syncProductMedia($product, Arr::get($remoteProduct, 'images', []));

        $this->syncProductVariants($product, $remoteProduct, $optionMap);
        
        $this->emitProgress('product_completed', [
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);
    }

    protected function upsertProduct(array $remoteProduct): Product
    {
        $remoteId = (int) $remoteProduct['id'];

        $product = Product::withTrashed()
            ->where('data->woocommerce_id', $remoteId)
            ->first();

        if (! $product) {
            $product = Product::withTrashed()
                ->where('slug', $remoteProduct['slug'] ?? Str::slug($remoteProduct['name'] ?? "wc-product-{$remoteId}"))
                ->first();
        }

        if (! $product) {
            $product = new Product();
            $product->uuid = (string) Str::uuid();
        } elseif ($product->trashed()) {
            $product->restore();
        }

        $isNew = ! $product->exists;

        $slug = $remoteProduct['slug'] ?? null;
        if ($slug === null || $slug === '') {
            $slug = Str::slug($remoteProduct['name'] ?? "wc-product-{$remoteId}");
        }
        $slug = $this->ensureUniqueSlug('products', $slug, $product->id);

        $payload = [
            'slug' => $slug,
            'type' => $this->mapProductType($remoteProduct['type'] ?? null),
            'name' => $remoteProduct['name'] ?? "Product {$remoteId}",
            'subtitle' => null,
            'sku' => $this->normalizeSku($remoteProduct['sku'] ?? null),
            'excerpt' => $this->sanitizeShortDescription($remoteProduct['short_description'] ?? null),
            'description' => $remoteProduct['description'] ?? null,
            'specifications' => $this->extractSpecifications($remoteProduct['attributes'] ?? []),
            'status' => $this->mapStatus($remoteProduct['status'] ?? null),
            'published_at' => $this->determinePublishedAt($remoteProduct),
            'meta_title' => null,
            'meta_description' => null,
        ];

        $product->fill($payload);

        $data = $product->data ?? [];
        $data['source'] = static::SOURCE;
        $data['woocommerce_id'] = $remoteId;
        $data['woocommerce'] = [
            'permalink' => $remoteProduct['permalink'] ?? null,
            'synced_at' => Carbon::now()->toIso8601String(),
            'raw_status' => $remoteProduct['status'] ?? null,
            'type' => $remoteProduct['type'] ?? null,
        ];
        $product->data = $data;

        if ($product->isDirty()) {
            $product->save();
            $this->stats['products'][$isNew ? 'created' : 'updated']++;
        }

        return $product;
    }

    protected function syncProductCategories(Product $product, array $remoteCategories): void
    {
        if (empty($remoteCategories)) {
            $product->categories()->detach();

            return;
        }

        $syncData = [];
        $position = 1;

        foreach ($remoteCategories as $remoteCategory) {
            $remoteId = (int) ($remoteCategory['id'] ?? 0);
            if ($remoteId <= 0) {
                continue;
            }

            $category = $this->categoryMap[$remoteId] ?? $this->createCategoryFromProductContext($remoteCategory);
            if (! $category) {
                continue;
            }

            $syncData[$category->id] = [
                'is_primary' => $position === 1,
                'position' => $position,
            ];
            $position++;
        }

        if (empty($syncData)) {
            $product->categories()->detach();

            return;
        }

        $product->categories()->sync($syncData);
    }

    /**
     * @param  array<int, mixed>  $remoteAttributes
     * @return array<string, array{option: ProductOption, values: array<string, ProductOptionValue>}>
     */
    protected function syncProductOptions(Product $product, array $remoteAttributes): array
    {
        $optionMap = [];
        $position = 1;

        $existingOptions = $product->options()->withTrashed()->get();

        foreach ($remoteAttributes as $attribute) {
            if (empty($attribute['variation'])) {
                continue;
            }

            $key = $this->attributeKey($attribute);
            $option = $this->findExistingOption($existingOptions, $attribute);

            if (! $option) {
                $option = new ProductOption();
                $option->product_id = $product->id;
            } elseif ($option->trashed()) {
                $option->restore();
            }

            $code = $this->buildOptionCode($attribute);
            $code = $this->ensureUniqueOptionCode($product, $code, $option->id);

            $option->fill([
                'code' => $code,
                'name' => $attribute['name'] ?? Str::title(str_replace('-', ' ', $code)),
                'input_type' => 'select',
                'is_required' => true,
                'position' => $position,
            ]);

            $data = $option->data ?? [];
            $data['source'] = static::SOURCE;
            if (! empty($attribute['id'])) {
                $data['woocommerce_attribute_id'] = (int) $attribute['id'];
            }
            $data['woocommerce_attribute_name'] = $attribute['name'] ?? null;
            $option->data = $data;

            if ($option->isDirty()) {
                $option->save();
            }

            $optionMap[$key] = [
                'option' => $option,
                'values' => $this->syncOptionValues($option, $attribute),
            ];

            $position++;
        }

        $optionIdsToKeep = array_map(
            fn (array $entry) => $entry['option']->id,
            $optionMap
        );

        $product->options()
            ->whereNotIn('id', empty($optionIdsToKeep) ? [0] : $optionIdsToKeep)
            ->delete();

        $this->optionCache[$product->id] = $optionMap;

        return $optionMap;
    }

    protected function syncProductMedia(Product $product, array $remoteImages): void
    {
        $existingMedia = $product->media()->withTrashed()->get();
        $keepIds = [];
        $shouldDownload = config('woocommerce.download_images', true);

        foreach ($remoteImages as $index => $remoteImage) {
            if (! is_array($remoteImage)) {
                continue;
            }

            $media = $this->findExistingMedia($existingMedia, $remoteImage);
            if (! $media) {
                $media = new ProductMedia(['product_id' => $product->id]);
            } elseif ($media->trashed()) {
                $media->restore();
            }

            // Determine disk and path for the image
            $disk = 'remote';
            $path = $remoteImage['src'] ?? $media->path;

            // Download image if configured to do so
            if ($shouldDownload && ! empty($remoteImage['src'])) {
                $localPath = $this->downloadAndStoreImage($remoteImage['src'], $product->id, $index);
                if ($localPath) {
                    $disk = 'public';
                    $path = $localPath;
                } else {
                    $this->log("<comment>Failed to download image {$remoteImage['src']}, using remote URL</comment>", OutputInterface::VERBOSITY_VERBOSE);
                }
            }

            $media->fill([
                'product_id' => $product->id,
                'type' => 'image',
                'disk' => $disk,
                'path' => $path,
                'is_primary' => $index === 0,
                'position' => $index + 1,
                'alt_text' => $remoteImage['alt'] ?? $remoteImage['name'] ?? null,
                'caption' => $remoteImage['name'] ?? null,
            ]);

            $data = $media->data ?? [];
            $data['source'] = static::SOURCE;
            if (! empty($remoteImage['id'])) {
                $data['woocommerce_image_id'] = (int) $remoteImage['id'];
            }
            $data['woocommerce_url'] = $remoteImage['src'] ?? null;
            $media->data = $data;

            if ($media->isDirty()) {
                $media->save();
                $this->stats['media'][$media->wasRecentlyCreated ? 'created' : 'updated']++;
            }

            $this->storeMediaReference($product, $media, $remoteImage);
            $keepIds[] = $media->id;
        }

        $query = $product->media();
        if (! empty($keepIds)) {
            $query->whereNotIn('id', $keepIds);
        }

        $query->get()->each(function (ProductMedia $media) {
            $media->delete();
            $this->stats['media']['deleted']++;
        });
    }

    /**
     * @param  array<int, mixed>  $optionMap
     */
    protected function syncProductVariants(Product $product, array $remoteProduct, array $optionMap): void
    {
        $remoteVariants = $this->resolveRemoteVariants($remoteProduct);

        $existingVariants = $product->variants()->withTrashed()->get();
        $variantsByWooId = $existingVariants->keyBy(fn (ProductVariant $variant) => (string) data_get($variant->data, 'woocommerce_id'));
        $variantsBySku = $existingVariants->keyBy('sku');

        $keepIds = [];
        $defaultVariantId = null;
        $defaultAttributeMap = $this->buildDefaultAttributeMap($remoteProduct['default_attributes'] ?? []);
        $productType = $remoteProduct['type'] ?? 'simple';

        foreach ($remoteVariants as $index => $remoteVariant) {
            $remoteVariantId = (int) ($remoteVariant['id'] ?? 0);

            $variant = null;
            if ($remoteVariantId && $variantsByWooId->has((string) $remoteVariantId)) {
                $variant = $variantsByWooId->get((string) $remoteVariantId);
            } elseif (! empty($remoteVariant['sku']) && $variantsBySku->has($remoteVariant['sku'])) {
                $variant = $variantsBySku->get($remoteVariant['sku']);
            }

            if (! $variant) {
                $variant = new ProductVariant(['product_id' => $product->id]);
            } elseif ($variant->trashed()) {
                $variant->restore();
            }

            $isDefault = $this->shouldMarkVariantAsDefault($remoteVariant, $defaultAttributeMap, $index, $productType);
            $payload = $this->mapVariantPayload($remoteVariant, $remoteProduct, $isDefault);
            $payload['product_id'] = $product->id;

            if (empty($payload['sku'])) {
                $payload['sku'] = $this->generateFallbackSku($product, $remoteVariant);
            }

            $variant->fill($payload);

            $data = $variant->data ?? [];
            $data['source'] = static::SOURCE;
            if ($remoteVariantId > 0) {
                $data['woocommerce_id'] = $remoteVariantId;
            }
            $data['synced_at'] = Carbon::now()->toIso8601String();
            $variant->data = $data;

            if ($variant->isDirty()) {
                $variant->save();
                $this->stats['variants'][$variant->wasRecentlyCreated ? 'created' : 'updated']++;
            }

            $keepIds[] = $variant->id;

            if ($isDefault) {
                $defaultVariantId = $variant->id;
            }

            $this->syncVariantOptionValues($variant, $remoteVariant['attributes'] ?? [], $optionMap);
            $this->syncVariantMedia($product, $variant, $remoteVariant['image'] ?? null);
        }

        $product->variants()
            ->whereNotIn('id', empty($keepIds) ? [0] : $keepIds)
            ->get()
            ->each(function (ProductVariant $variant) {
                $variant->delete();
                $this->stats['variants']['deleted']++;
            });

        if ($defaultVariantId === null && ! empty($keepIds)) {
            $defaultVariantId = $keepIds[0];
        }

        if ($defaultVariantId !== $product->default_variant_id) {
            $product->default_variant_id = $defaultVariantId;
            $product->save();
        }
    }

    protected function syncVariantOptionValues(ProductVariant $variant, array $remoteAttributes, array $optionMap): void
    {
        if (empty($remoteAttributes)) {
            $variant->optionValues()->sync([]);

            return;
        }

        $optionValueIds = [];

        foreach ($remoteAttributes as $attribute) {
            $key = $this->attributeKey($attribute);
            $valueKey = $this->valueKey($attribute['option'] ?? null);

            if ($valueKey === '' || ! isset($optionMap[$key])) {
                continue;
            }

            $optionEntry = $optionMap[$key];
            $optionValue = $optionEntry['values'][$valueKey] ?? null;

            if (! $optionValue) {
                $optionValue = $this->createOptionValue($optionEntry['option'], $attribute['option'] ?? '', count($optionEntry['values']) + 1);
                $optionMap[$key]['values'][$valueKey] = $optionValue;
                $this->optionCache[$variant->product_id][$key]['values'][$valueKey] = $optionValue;
            }

            $optionValueIds[] = $optionValue->id;
        }

        $variant->optionValues()->sync(array_unique($optionValueIds));
    }

    protected function syncVariantMedia(Product $product, ProductVariant $variant, ?array $remoteImage): void
    {
        if (empty($remoteImage) || empty($remoteImage['src'])) {
            return;
        }

        $media = $this->resolveMediaForVariant($product, $remoteImage);
        if (! $media) {
            $shouldDownload = config('woocommerce.download_images', true);
            $disk = 'remote';
            $path = $remoteImage['src'];

            // Download image if configured to do so
            if ($shouldDownload) {
                $localPath = $this->downloadAndStoreImage($remoteImage['src'], $product->id, 'variant-'.$variant->id);
                if ($localPath) {
                    $disk = 'public';
                    $path = $localPath;
                } else {
                    $this->log("<comment>Failed to download variant image {$remoteImage['src']}, using remote URL</comment>", OutputInterface::VERBOSITY_VERBOSE);
                }
            }

            $media = $product->media()->create([
                'type' => 'image',
                'disk' => $disk,
                'path' => $path,
                'is_primary' => false,
                'position' => (int) $product->media()->max('position') + 1,
                'alt_text' => $remoteImage['alt'] ?? $remoteImage['name'] ?? null,
                'caption' => $remoteImage['name'] ?? null,
                'data' => [
                    'source' => static::SOURCE,
                    'woocommerce_image_id' => $remoteImage['id'] ?? null,
                    'woocommerce_url' => $remoteImage['src'],
                ],
            ]);

            $this->stats['media']['created']++;
            $this->storeMediaReference($product, $media, $remoteImage);
        }

        if ($media->product_variant_id !== $variant->id) {
            $media->product_variant_id = $variant->id;
            $media->save();
            $this->stats['media']['updated']++;
        }
    }

    protected function resolveMediaForVariant(Product $product, array $remoteImage): ?ProductMedia
    {
        $productId = $product->id;
        $cache = $this->productMediaCache[$productId] ?? [];

        if (! empty($remoteImage['id']) && isset($cache['id:'.$remoteImage['id']])) {
            return $cache['id:'.$remoteImage['id']];
        }

        if (! empty($remoteImage['src']) && isset($cache['src:'.$remoteImage['src']])) {
            return $cache['src:'.$remoteImage['src']];
        }

        $existing = $product->media()->withTrashed()->get();

        return $this->findExistingMedia($existing, $remoteImage);
    }

    protected function resolveRemoteVariants(array $remoteProduct): array
    {
        $productType = $remoteProduct['type'] ?? 'simple';
        $remoteId = (int) ($remoteProduct['id'] ?? 0);

        if ($productType !== 'variable' || $remoteId <= 0) {
            return [$this->mapSimpleProductToVariant($remoteProduct)];
        }

        $variants = [];
        foreach ($this->client->getProductVariations($remoteId) as $variation) {
            if (is_array($variation)) {
                $variants[] = $variation;
            }
        }

        return $variants ?: [$this->mapSimpleProductToVariant($remoteProduct)];
    }

    protected function mapSimpleProductToVariant(array $product): array
    {
        return [
            'id' => $product['id'] ?? null,
            'sku' => $product['sku'] ?? null,
            'status' => $product['status'] ?? null,
            'price' => $product['price'] ?? null,
            'regular_price' => $product['regular_price'] ?? null,
            'sale_price' => $product['sale_price'] ?? null,
            'stock_quantity' => $product['stock_quantity'] ?? null,
            'manage_stock' => $product['manage_stock'] ?? false,
            'backorders' => $product['backorders'] ?? 'no',
            'stock_status' => $product['stock_status'] ?? null,
            'weight' => $product['weight'] ?? null,
            'dimensions' => $product['dimensions'] ?? [],
            'shipping_required' => $product['shipping_required'] ?? true,
            'date_created_gmt' => $product['date_created_gmt'] ?? null,
            'date_modified_gmt' => $product['date_modified_gmt'] ?? null,
            'image' => Arr::first($product['images'] ?? []),
            'attributes' => [],
        ];
    }

    protected function mapVariantPayload(array $remoteVariant, array $remoteProduct, bool $isDefault): array
    {
        $price = $this->resolvePrice($remoteVariant, $remoteProduct);

        return [
            'sku' => $this->normalizeSku($remoteVariant['sku'] ?? null),
            'barcode' => $this->extractBarcode($remoteVariant),
            'status' => $this->mapStatus($remoteVariant['status'] ?? $remoteProduct['status'] ?? null),
            'price' => $price,
            'compare_at_price' => $this->resolveCompareAtPrice($remoteVariant, $remoteProduct, $price),
            'cost' => null,
            'currency' => $this->currency,
            'inventory_sku' => $this->normalizeSku($remoteVariant['sku'] ?? null),
            'inventory_policy' => $this->mapInventoryPolicy($remoteVariant['backorders'] ?? $remoteProduct['backorders'] ?? 'no'),
            'inventory_quantity' => $this->resolveInventoryQuantity($remoteVariant, $remoteProduct),
            'track_inventory' => (bool) ($remoteVariant['manage_stock'] ?? $remoteProduct['manage_stock'] ?? false),
            'weight' => $this->toFloat($remoteVariant['weight'] ?? null),
            'weight_unit' => null,
            'length' => $this->toFloat(Arr::get($remoteVariant, 'dimensions.length')),
            'width' => $this->toFloat(Arr::get($remoteVariant, 'dimensions.width')),
            'height' => $this->toFloat(Arr::get($remoteVariant, 'dimensions.height')),
            'dimension_unit' => null,
            'is_default' => $isDefault,
            'requires_shipping' => (bool) ($remoteVariant['shipping_required'] ?? true),
            'requires_serial' => false,
            'published_at' => $this->parseDate($remoteVariant['date_created_gmt'] ?? $remoteProduct['date_created_gmt'] ?? null),
            'data' => $remoteVariant['data'] ?? null,
        ];
    }

    protected function resolvePrice(array $remoteVariant, array $remoteProduct): float
    {
        $candidates = [
            $remoteVariant['price'] ?? null,
            $remoteVariant['sale_price'] ?? null,
            $remoteVariant['regular_price'] ?? null,
            $remoteProduct['price'] ?? null,
            $remoteProduct['sale_price'] ?? null,
            $remoteProduct['regular_price'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $value = $this->toFloat($candidate);
            if ($value !== null) {
                return max($value, 0);
            }
        }

        return 0.0;
    }

    protected function resolveCompareAtPrice(array $remoteVariant, array $remoteProduct, float $determinedPrice): ?float
    {
        $regular = $this->toFloat($remoteVariant['regular_price'] ?? $remoteProduct['regular_price'] ?? null);
        $sale = $this->toFloat($remoteVariant['sale_price'] ?? $remoteProduct['sale_price'] ?? null);

        if ($regular !== null && $regular > $determinedPrice && ($sale === null || $sale < $regular)) {
            return $regular;
        }

        return null;
    }

    protected function resolveInventoryQuantity(array $remoteVariant, array $remoteProduct): int
    {
        if (! empty($remoteVariant['manage_stock'])) {
            return (int) ($remoteVariant['stock_quantity'] ?? 0);
        }

        if (! empty($remoteProduct['manage_stock'])) {
            return (int) ($remoteProduct['stock_quantity'] ?? 0);
        }

        return 0;
    }

    /**
     * @param  array<int, mixed>  $defaultAttributes
     * @return array<string, string>
     */
    protected function buildDefaultAttributeMap(array $defaultAttributes): array
    {
        $map = [];

        foreach ($defaultAttributes as $attribute) {
            $key = $this->attributeKey($attribute);
            $valueKey = $this->valueKey($attribute['option'] ?? null);

            if ($valueKey !== '') {
                $map[$key] = $valueKey;
            }
        }

        return $map;
    }

    protected function shouldMarkVariantAsDefault(array $remoteVariant, array $defaultAttributeMap, int $index, string $productType): bool
    {
        if ($productType !== 'variable') {
            return $index === 0;
        }

        if (empty($defaultAttributeMap)) {
            return $index === 0;
        }

        $variantAttributes = [];
        foreach ($remoteVariant['attributes'] ?? [] as $attribute) {
            $variantAttributes[$this->attributeKey($attribute)] = $this->valueKey($attribute['option'] ?? null);
        }

        foreach ($defaultAttributeMap as $key => $value) {
            if (! isset($variantAttributes[$key]) || $variantAttributes[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    protected function extractBarcode(array $remoteVariant): ?string
    {
        foreach ($remoteVariant['meta_data'] ?? [] as $meta) {
            $key = strtolower((string) ($meta['key'] ?? ''));
            if (in_array($key, ['barcode', '_barcode', 'gtin', '_gtin'], true)) {
                $value = trim((string) ($meta['value'] ?? ''));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    protected function mapInventoryPolicy(?string $backorders): string
    {
        return match (strtolower((string) $backorders)) {
            'notify', 'yes' => 'allow',
            default => 'deny',
        };
    }

    protected function generateFallbackSku(Product $product, array $remoteVariant): string
    {
        $base = ! empty($remoteVariant['id'])
            ? 'woo-'.$remoteVariant['id']
            : 'woo-'.$product->id;

        $candidate = $base;
        $suffix = 1;

        while ($product->variants()->withTrashed()->where('sku', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    protected function findExistingOption(EloquentCollection $options, array $attribute): ?ProductOption
    {
        $attributeId = (int) ($attribute['id'] ?? 0);
        if ($attributeId > 0) {
            $option = $options->first(function (ProductOption $option) use ($attributeId) {
                return (int) data_get($option->data, 'woocommerce_attribute_id') === $attributeId;
            });

            if ($option) {
                return $option;
            }
        }

        $code = $this->buildOptionCode($attribute);

        return $options->firstWhere('code', $code);
    }

    protected function syncOptionValues(ProductOption $option, array $attribute): array
    {
        $existingValues = $option->values()->withTrashed()->get();
        $valuesMap = [];
        $keepIds = [];
        $position = 1;

        foreach ($attribute['options'] ?? [] as $rawValue) {
            $displayValue = trim((string) $rawValue);
            if ($displayValue === '') {
                continue;
            }

            $valueKey = $this->valueKey($displayValue);
            $valueModel = $this->findExistingOptionValue($existingValues, $displayValue);

            if (! $valueModel) {
                $valueModel = new ProductOptionValue(['product_option_id' => $option->id]);
            } elseif ($valueModel->trashed()) {
                $valueModel->restore();
            }

            $slug = Str::slug($displayValue);
            if ($slug === '') {
                $slug = substr(md5($displayValue), 0, 12);
            }
            $slug = $this->ensureUniqueOptionValueSlug($option, $slug, $valueModel->id);

            $valueModel->fill([
                'product_option_id' => $option->id,
                'value' => $slug,
                'display_value' => $displayValue,
                'position' => $position,
            ]);

            $data = $valueModel->data ?? [];
            $data['source'] = static::SOURCE;
            $data['woocommerce_value'] = $displayValue;
            $valueModel->data = $data;

            if ($valueModel->isDirty()) {
                $valueModel->save();
            }

            $keepIds[] = $valueModel->id;
            $valuesMap[$valueKey] = $valueModel;
            $position++;
        }

        $option->values()
            ->whereNotIn('id', empty($keepIds) ? [0] : $keepIds)
            ->delete();

        return $valuesMap;
    }

    protected function findExistingOptionValue(EloquentCollection $values, string $displayValue): ?ProductOptionValue
    {
        $valueKey = $this->valueKey($displayValue);

        return $values->first(function (ProductOptionValue $value) use ($displayValue, $valueKey) {
            return data_get($value->data, 'woocommerce_value') === $displayValue
                || $value->display_value === $displayValue
                || $value->value === $valueKey;
        });
    }

    protected function createOptionValue(ProductOption $option, string $displayValue, int $position): ProductOptionValue
    {
        $slug = Str::slug($displayValue);
        if ($slug === '') {
            $slug = substr(md5($displayValue), 0, 12);
        }

        $slug = $this->ensureUniqueOptionValueSlug($option, $slug);

        return $option->values()->create([
            'value' => $slug,
            'display_value' => $displayValue,
            'position' => $position,
            'data' => [
                'source' => static::SOURCE,
                'woocommerce_value' => $displayValue,
            ],
        ]);
    }

    protected function findExistingMedia(EloquentCollection $mediaCollection, array $remoteImage): ?ProductMedia
    {
        if (! empty($remoteImage['id'])) {
            $media = $mediaCollection->first(function (ProductMedia $media) use ($remoteImage) {
                return (int) data_get($media->data, 'woocommerce_image_id') === (int) $remoteImage['id'];
            });

            if ($media) {
                return $media;
            }
        }

        if (! empty($remoteImage['src'])) {
            return $mediaCollection->firstWhere('path', $remoteImage['src']);
        }

        return null;
    }

    protected function storeMediaReference(Product $product, ProductMedia $media, array $remoteImage): void
    {
        $productId = $product->id;
        $this->productMediaCache[$productId] ??= [];

        if (! empty($remoteImage['id'])) {
            $this->productMediaCache[$productId]['id:'.$remoteImage['id']] = $media;
        }

        if (! empty($remoteImage['src'])) {
            $this->productMediaCache[$productId]['src:'.$remoteImage['src']] = $media;
        }
    }

    protected function syncCategories(): void
    {
        $categories = [];

        foreach ($this->client->getCategories(['per_page' => 100]) as $remoteCategory) {
            if (! is_array($remoteCategory) || empty($remoteCategory['id'])) {
                continue;
            }

            $categories[(int) $remoteCategory['id']] = $remoteCategory;
        }

        foreach (array_keys($categories) as $remoteId) {
            $this->ensureCategory($remoteId, $categories);
        }
    }

    protected function ensureCategory(int $remoteId, array $categories, array &$stack = []): ?Category
    {
        if (isset($this->categoryMap[$remoteId])) {
            return $this->categoryMap[$remoteId];
        }

        if (isset($stack[$remoteId])) {
            return null;
        }

        $categoryData = $categories[$remoteId] ?? null;
        if (! $categoryData) {
            return null;
        }

        $stack[$remoteId] = true;

        $parent = null;
        $parentId = (int) ($categoryData['parent'] ?? 0);
        if ($parentId > 0) {
            $parent = $this->ensureCategory($parentId, $categories, $stack);
        }

        $category = Category::withTrashed()
            ->where('data->woocommerce_id', $remoteId)
            ->first();

        if (! $category) {
            $category = Category::withTrashed()
                ->where('slug', $categoryData['slug'] ?? Str::slug($categoryData['name'] ?? "wc-category-{$remoteId}"))
                ->first();
        }

        if (! $category) {
            $category = new Category();
            $category->type = 'catalog';
        } elseif ($category->trashed()) {
            $category->restore();
        }

        $slug = $categoryData['slug'] ?? Str::slug($categoryData['name'] ?? "wc-category-{$remoteId}");
        $slug = $this->ensureUniqueSlug('categories', $slug, $category->id);

        $category->fill([
            'parent_id' => $parent?->id,
            'name' => $categoryData['name'] ?? "Category {$remoteId}",
            'slug' => $slug,
            'description' => $categoryData['description'] ?? null,
            'image_url' => Arr::get($categoryData, 'image.src'),
            'status' => ($categoryData['display'] ?? 'visible') === 'hidden' ? 'draft' : 'published',
            'position' => $categoryData['menu_order'] ?? 0,
            'is_visible' => ($categoryData['display'] ?? 'visible') !== 'hidden',
        ]);

        $data = $category->data ?? [];
        $data['source'] = static::SOURCE;
        $data['woocommerce_id'] = $remoteId;
        $data['woocommerce_slug'] = $categoryData['slug'] ?? null;
        $category->data = $data;

        if ($category->isDirty()) {
            $category->save();
            $this->stats['categories'][$category->wasRecentlyCreated ? 'created' : 'updated']++;
        }

        $this->categoryMap[$remoteId] = $category;
        unset($stack[$remoteId]);

        return $category;
    }

    protected function createCategoryFromProductContext(array $categoryData): ?Category
    {
        $remoteId = (int) ($categoryData['id'] ?? 0);
        if ($remoteId <= 0) {
            return null;
        }

        $category = Category::withTrashed()
            ->where('data->woocommerce_id', $remoteId)
            ->first();

        if (! $category) {
            $category = new Category();
            $category->type = 'catalog';
        } elseif ($category->trashed()) {
            $category->restore();
        }

        $slug = $categoryData['slug'] ?? Str::slug($categoryData['name'] ?? "wc-category-{$remoteId}");
        $slug = $this->ensureUniqueSlug('categories', $slug, $category->id);

        $category->fill([
            'name' => $categoryData['name'] ?? "Category {$remoteId}",
            'slug' => $slug,
            'description' => $categoryData['description'] ?? null,
            'status' => 'published',
            'position' => $categoryData['menu_order'] ?? 0,
            'is_visible' => true,
        ]);

        $data = $category->data ?? [];
        $data['source'] = static::SOURCE;
        $data['woocommerce_id'] = $remoteId;
        $category->data = $data;

        if ($category->isDirty()) {
            $category->save();
            $this->stats['categories'][$category->wasRecentlyCreated ? 'created' : 'updated']++;
        }

        $this->categoryMap[$remoteId] = $category;

        return $category;
    }

    protected function hydrateCategoryCache(): void
    {
        $this->categoryMap = Category::withTrashed()
            ->get()
            ->filter(fn (Category $category) => data_get($category->data, 'woocommerce_id'))
            ->keyBy(fn (Category $category) => (int) data_get($category->data, 'woocommerce_id'))
            ->all();
    }

    protected function mapStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'publish', 'published' => 'published',
            'trash', 'archived' => 'archived',
            default => 'draft',
        };
    }

    protected function mapProductType(?string $type): string
    {
        return match (strtolower((string) $type)) {
            'variable' => 'configurable',
            default => 'standard',
        };
    }

    protected function sanitizeShortDescription(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $text = trim(strip_tags($value));

        return $text !== '' ? $text : null;
    }

    protected function determinePublishedAt(array $remoteProduct): ?Carbon
    {
        if ($this->mapStatus($remoteProduct['status'] ?? null) !== 'published') {
            return null;
        }

        return $this->parseDate($remoteProduct['date_created_gmt'] ?? $remoteProduct['date_created'] ?? null);
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    protected function normalizeSku(?string $sku): ?string
    {
        $sku = $sku !== null ? trim($sku) : null;

        return $sku === '' ? null : $sku;
    }

    protected function extractSpecifications(array $attributes): ?array
    {
        $specifications = [];

        foreach ($attributes as $attribute) {
            if (! empty($attribute['variation'])) {
                continue;
            }

            $name = trim((string) ($attribute['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $options = array_values(array_filter(array_map(
                fn ($option) => trim((string) $option),
                $attribute['options'] ?? []
            )));

            if (empty($options)) {
                continue;
            }

            $specifications[$name] = count($options) === 1 ? $options[0] : $options;
        }

        return empty($specifications) ? null : $specifications;
    }

    protected function attributeKey(array $attribute): string
    {
        $id = (int) ($attribute['id'] ?? 0);
        if ($id > 0) {
            return 'id:'.$id;
        }

        return 'name:'.Str::slug($attribute['name'] ?? $attribute['slug'] ?? 'attribute');
    }

    protected function valueKey(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    protected function buildOptionCode(array $attribute): string
    {
        $slug = $attribute['slug'] ?? $attribute['name'] ?? null;
        $code = Str::slug((string) $slug);

        return $code !== '' ? $code : 'option';
    }

    protected function ensureUniqueOptionCode(Product $product, string $code, ?int $ignoreId = null): string
    {
        $base = $code !== '' ? $code : 'option';
        $candidate = $base;
        $suffix = 1;

        while ($product->options()->withTrashed()
            ->where('code', $candidate)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    protected function ensureUniqueOptionValueSlug(ProductOption $option, string $slug, ?int $ignoreId = null): string
    {
        $base = $slug !== '' ? $slug : 'value';
        $candidate = $base;
        $suffix = 1;

        while ($option->values()->withTrashed()
            ->where('value', $candidate)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    protected function ensureUniqueSlug(string $table, string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug);
        if ($base === '') {
            $base = 'item';
        }

        $candidate = $base;
        $suffix = 1;

        while (
            DB::table($table)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    protected function resetStats(): void
    {
        $this->stats = [
            'categories' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'products' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'variants' => ['created' => 0, 'updated' => 0, 'deleted' => 0],
            'media' => ['created' => 0, 'updated' => 0, 'deleted' => 0],
        ];
    }

    protected function toFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $filtered = filter_var(
            (string) $value,
            FILTER_SANITIZE_NUMBER_FLOAT,
            FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
        );

        if ($filtered === false || $filtered === '') {
            return null;
        }

        return (float) $filtered;
    }

    protected function formatDateTime(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->toIso8601String();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toIso8601String();
        }

        return Carbon::parse((string) $value)->toIso8601String();
    }

    protected function log(string $message, int $verbosity = OutputInterface::VERBOSITY_NORMAL): void
    {
        if ($this->output) {
            $this->output->writeln($message, $verbosity);
        }
    }

    protected function emitProgress(string $event, array $data = []): void
    {
        if ($this->progressCallback) {
            ($this->progressCallback)($event, $data);
        }
    }

    /**
     * Download an image from a remote URL and store it locally.
     *
     * @param  string  $url  The remote image URL
     * @param  int  $productId  The product ID for organizing storage
     * @param  string|int  $identifier  Unique identifier for the image (index, variant ID, etc.)
     * @return string|null  The local storage path or null if download failed
     */
    protected function downloadAndStoreImage(string $url, int $productId, string|int $identifier): ?string
    {
        try {
            // Download the image
            $response = Http::timeout(30)
                ->withOptions(['verify' => config('woocommerce.verify', true)])
                ->get($url);

            if (! $response->successful()) {
                Log::warning('Failed to download image from WooCommerce.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'product_id' => $productId,
                ]);

                return null;
            }

            // Get the image content
            $imageContent = $response->body();

            if (empty($imageContent)) {
                return null;
            }

            // Determine file extension from URL or content type
            $extension = $this->getImageExtension($url, $response->header('Content-Type'));

            // Generate a unique filename
            $filename = $this->generateImageFilename($url, $identifier, $extension);

            // Store the image in the public disk
            $path = "products/{$productId}/{$filename}";

            if (Storage::disk('public')->put($path, $imageContent)) {
                return $path;
            }

            return null;
        } catch (Throwable $exception) {
            Log::error('Exception while downloading WooCommerce image.', [
                'url' => $url,
                'product_id' => $productId,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Extract or determine the file extension for an image.
     */
    protected function getImageExtension(string $url, ?string $contentType): string
    {
        // Try to get extension from URL
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension && in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            return strtolower($extension);
        }

        // Try to determine from content type
        if ($contentType) {
            $mimeMap = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
            ];

            foreach ($mimeMap as $mime => $ext) {
                if (str_contains($contentType, $mime)) {
                    return $ext;
                }
            }
        }

        // Default to jpg
        return 'jpg';
    }

    /**
     * Generate a unique filename for the downloaded image.
     */
    protected function generateImageFilename(string $url, string|int $identifier, string $extension): string
    {
        // Try to extract original filename from URL
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $originalName = pathinfo($path, PATHINFO_FILENAME);

        // Sanitize the original name
        if ($originalName) {
            $sanitized = Str::slug($originalName);
            if ($sanitized && strlen($sanitized) <= 100) {
                return "{$sanitized}-{$identifier}.{$extension}";
            }
        }

        // Generate a hash-based name if original name is not usable
        $hash = substr(md5($url), 0, 12);

        return "{$hash}-{$identifier}.{$extension}";
    }
}
