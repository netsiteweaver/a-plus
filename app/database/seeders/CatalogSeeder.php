<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductMedia;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\RelatedProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $brands = $this->seedBrands();

            $categories = $this->seedCategories();

            [$attributes, $attributeValues] = $this->seedAttributes();

            // ============================================================
            // TEMPORARILY DISABLED: Product creation for WooCommerce import
            // Uncomment the section below to re-enable product seeding
            // ============================================================
            
            /*
            $allProducts = collect();

            foreach ($categories as $category) {
                $products = Product::factory()
                    ->count(6)
                    ->create([
                        'brand_id' => $brands->random()->id,
                    ]);

                foreach ($products as $index => $product) {
                    $product->categories()->attach($category->id, [
                        'is_primary' => true,
                        'position' => $index + 1,
                    ]);

                    if ($categories->count() > 1) {
                        $secondary = $categories->where('id', '!=', $category->id)->random();
                        $product->categories()->attach($secondary->id, [
                            'is_primary' => false,
                            'position' => $index + 1,
                        ]);
                    }

                    $this->seedProductOptionsAndVariants($product);
                    $this->seedProductMedia($product);
                    $this->seedProductAttributes($product, $attributes, $attributeValues);

                    $allProducts->push($product->fresh('variants'));
                }
            }

            $this->seedRelatedProducts($allProducts);
            */
        });
    }

    protected function seedBrands()
    {
        $brandNames = [
            'Apex Systems',
            'Nimbus Labs',
            'Lumenware',
            'Vertex Dynamics',
        ];

        return collect($brandNames)->map(function ($name) {
            return Brand::factory()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'meta_title' => $name . ' - ' . config('app.name'),
            ]);
        });
    }

    protected function seedCategories()
    {
        $definitions = [
            [
                'name' => 'Laptops & Computers',
                'slug' => 'laptops',
                'description' => 'Creator rigs, workstations, and AI-ready ultraportables tuned for real-time rendering and pro editing.',
            ],
            [
                'name' => 'Audio & Wearables',
                'slug' => 'audio',
                'description' => 'Spatial audio earbuds, ANC headsets, and biometric wearables crafted for immersive experiences.',
            ],
            [
                'name' => 'Smart Home',
                'slug' => 'smart-home',
                'description' => 'Intelligent climate, lighting, and security ecosystems with Matter-ready hubs.',
            ],
            [
                'name' => 'Creator Accessories',
                'slug' => 'creator-accessories',
                'description' => 'Monitors, docks, and input devices engineered for color-accurate creative workflows.',
            ],
        ];

        return collect($definitions)->map(function ($data, $index) {
            return Category::factory()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'image_url' => sprintf('https://images.unsplash.com/photo-15%s?auto=format&fit=crop&w=1200&q=80', 900000000000 + $index + rand(1, 500)),
                'position' => $index + 1,
                'data' => [
                    'accent' => Arr::random(['from $999', 'bundle & save', 'staff picks', 'ships free']),
                ],
            ]);
        });
    }

    protected function seedAttributes()
    {
        $attributeDefinitions = [
            'processor' => [
                'Intel Core Ultra 9 185H',
                'AMD Ryzen 9 8945HX',
                'Apple M3 Max',
            ],
            'graphics' => [
                'NVIDIA GeForce RTX 4090 16GB',
                'NVIDIA GeForce RTX 4080 12GB',
                'AMD Radeon RX 7900M',
            ],
            'memory' => [
                '32GB LPDDR5X',
                '64GB DDR5 6000MHz',
                '96GB DDR5 5600MHz',
            ],
            'display' => [
                '16" OLED 4K 240Hz',
                '17" mini-LED 165Hz',
                '15.6" QHD+ 240Hz',
            ],
        ];

        $attributes = collect();
        $attributeValues = collect();

        foreach ($attributeDefinitions as $code => $values) {
            $attribute = Attribute::firstOrCreate(
                ['code' => $code],
                [
                    'name' => Str::title(str_replace('_', ' ', $code)),
                    'type' => 'select',
                    'unit' => null,
                    'is_filterable' => true,
                    'is_required' => false,
                    'data' => [],
                ]
            );

            $attributes->put($code, $attribute);

            $valuesCollection = collect($values)->map(function ($value, $index) use ($attribute) {
                return AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'value' => $value,
                    ],
                    [
                        'display_value' => $value,
                        'numeric_value' => null,
                        'data' => ['position' => $index + 1],
                    ]
                );
            });

            $attributeValues->put($code, $valuesCollection);
        }

        return [$attributes, $attributeValues];
    }

    protected function seedProductOptionsAndVariants(Product $product): void
    {
        $colorOption = $product->options()->create([
            'code' => 'color',
            'name' => 'Color',
            'input_type' => 'swatch',
            'is_required' => true,
            'position' => 1,
            'data' => [],
        ]);

        $storageOption = $product->options()->create([
            'code' => 'storage',
            'name' => 'Storage',
            'input_type' => 'select',
            'is_required' => true,
            'position' => 2,
            'data' => [],
        ]);

        $colorPalette = [
            ['label' => 'Obsidian black', 'hex' => '#101820'],
            ['label' => 'Titanium silver', 'hex' => '#94989E'],
            ['label' => 'Glacier white', 'hex' => '#f1f5f9'],
        ];

        $storageVariants = ['1TB NVMe', '2TB NVMe', '4TB NVMe'];

        $colorValues = collect($colorPalette)->map(function ($color, $index) use ($colorOption) {
            return $colorOption->values()->create([
                'value' => Str::slug($color['label']),
                'display_value' => Str::title($color['label']),
                'hex_value' => $color['hex'],
                'thumbnail_url' => null,
                'position' => $index + 1,
                'data' => ['swatch' => $color['hex']],
            ]);
        });

        $storageValues = collect($storageVariants)->map(function ($storage, $index) use ($storageOption) {
            return $storageOption->values()->create([
                'value' => Str::slug($storage),
                'display_value' => $storage,
                'hex_value' => null,
                'thumbnail_url' => null,
                'position' => $index + 1,
                'data' => [],
            ]);
        });

        $basePrice = $this->fakerPrice(1899, 3299);
        $variantCollection = collect();

        foreach ($colorValues as $colorIndex => $colorValue) {
            foreach ($storageValues as $storageIndex => $storageValue) {
                $priceModifier = ($storageIndex * 180) + ($colorIndex * 90);
                $variant = $product->variants()->create([
                    'sku' => strtoupper(Str::random(12)),
                    'barcode' => (string) Str::uuid(),
                    'status' => 'published',
                    'price' => $basePrice + $priceModifier,
                    'compare_at_price' => $basePrice + $priceModifier + 200,
                    'cost' => $basePrice - 400 + $priceModifier / 2,
                    'currency' => 'USD',
                    'inventory_sku' => strtoupper(Str::random(10)),
                    'inventory_policy' => 'deny',
                    'inventory_quantity' => rand(15, 120),
                    'track_inventory' => true,
                    'weight' => rand(1500, 2100) / 1000,
                    'weight_unit' => 'kg',
                    'length' => rand(140, 180) / 10,
                    'width' => rand(90, 120) / 10,
                    'height' => rand(15, 25) / 10,
                    'dimension_unit' => 'inch',
                    'is_default' => false,
                    'requires_shipping' => true,
                    'requires_serial' => false,
                    'published_at' => now()->subDays(rand(5, 80)),
                    'data' => [],
                ]);

                $variant->optionValues()->attach([$colorValue->id, $storageValue->id]);
                $variantCollection->push($variant);
            }
        }

        $defaultVariant = $variantCollection->first();
        if ($defaultVariant) {
            $defaultVariant->update(['is_default' => true]);
            $product->update(['default_variant_id' => $defaultVariant->id]);
        }
    }

    protected function seedProductMedia(Product $product): void
    {
        $imagePool = [
            'https://images.unsplash.com/photo-1593642634315-48f5414c3ad9?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1517059224940-d4af9eec41e5?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1600&q=80',
        ];

        foreach (array_slice($imagePool, 0, 3) as $index => $url) {
            $product->media()->create([
                'type' => 'image',
                'disk' => 'remote',
                'path' => $url,
                'is_primary' => $index === 0,
                'position' => $index + 1,
                'alt_text' => $product->name . ' imagery',
                'caption' => null,
                'data' => [],
            ]);
        }
    }

    protected function seedProductAttributes(Product $product, $attributes, $attributeValues): void
    {
        foreach ($attributes as $code => $attribute) {
            $value = $attributeValues->get($code)->random();

            ProductAttributeValue::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'attribute_id' => $attribute->id,
                    'attribute_value_id' => $value->id,
                ],
                [
                    'value_text' => $value->display_value,
                ]
            );
        }
    }

    protected function seedRelatedProducts($products): void
    {
        $products->each(function (Product $product) use ($products) {
            $pool = $products->where('id', '!=', $product->id);
            if ($pool->isEmpty()) {
                return;
            }

            $count = min(3, $pool->count());
            $related = $count === 1 ? collect([$pool->random()]) : $pool->random($count);

            collect($related)->values()->each(function (Product $item, int $position) use ($product) {
                RelatedProduct::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'related_product_id' => $item->id,
                        'relation_type' => $position === 0 ? 'upsell' : 'related',
                    ],
                    [
                        'position' => $position + 1,
                    ]
                );
            });
        });
    }

    protected function fakerPrice(int $min, int $max): float
    {
        return rand($min, $max);
    }
}
