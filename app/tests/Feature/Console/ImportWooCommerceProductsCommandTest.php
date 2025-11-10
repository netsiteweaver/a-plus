<?php

namespace Tests\Feature\Console;

use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportWooCommerceProductsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_products_from_woocommerce(): void
    {
        config()->set('woocommerce.url', 'https://example.test/wp-json/wc/v3');
        config()->set('woocommerce.consumer_key', 'ck_test');
        config()->set('woocommerce.consumer_secret', 'cs_test');
        config()->set('woocommerce.per_page', 50);
        config()->set('woocommerce.statuses', ['publish', 'draft']);
        config()->set('woocommerce.default_currency', 'USD');

        $simpleProduct = [
            'id' => 101,
            'name' => 'Simple Product',
            'slug' => 'simple-product',
            'type' => 'simple',
            'status' => 'publish',
            'sku' => 'SP-001',
            'short_description' => '<p>Short description</p>',
            'description' => '<p>Long description</p>',
            'price' => '19.99',
            'regular_price' => '19.99',
            'sale_price' => '',
            'manage_stock' => true,
            'stock_quantity' => 5,
            'stock_status' => 'instock',
            'categories' => [
                [
                    'id' => 11,
                    'name' => 'Category A',
                    'slug' => 'category-a',
                ],
            ],
            'images' => [
                [
                    'id' => 201,
                    'src' => 'https://example.test/media/simple.jpg',
                    'name' => 'Simple image',
                    'alt' => 'Simple alt',
                ],
            ],
            'attributes' => [
                [
                    'id' => 31,
                    'name' => 'Material',
                    'slug' => 'material',
                    'variation' => false,
                    'options' => ['Aluminum'],
                ],
            ],
        ];

        $variableProduct = [
            'id' => 102,
            'name' => 'Variable Product',
            'slug' => 'variable-product',
            'type' => 'variable',
            'status' => 'publish',
            'short_description' => '',
            'description' => '<p>Variable description</p>',
            'categories' => [
                [
                    'id' => 12,
                    'name' => 'Category B',
                    'slug' => 'category-b',
                ],
            ],
            'images' => [
                [
                    'id' => 202,
                    'src' => 'https://example.test/media/variable-main.jpg',
                    'name' => 'Variable main',
                    'alt' => 'Variable alt',
                ],
            ],
            'attributes' => [
                [
                    'id' => 41,
                    'name' => 'Color',
                    'slug' => 'pa_color',
                    'variation' => true,
                    'options' => ['Red', 'Blue'],
                ],
                [
                    'id' => 42,
                    'name' => 'Memory',
                    'slug' => 'pa_memory',
                    'variation' => true,
                    'options' => ['64GB', '128GB'],
                ],
            ],
            'default_attributes' => [
                [
                    'id' => 41,
                    'name' => 'Color',
                    'option' => 'Red',
                ],
                [
                    'id' => 42,
                    'name' => 'Memory',
                    'option' => '64GB',
                ],
            ],
        ];

        $variations = [
            [
                'id' => 1001,
                'parent_id' => 102,
                'status' => 'publish',
                'sku' => 'VP-RED-64',
                'price' => '199.99',
                'regular_price' => '249.99',
                'sale_price' => '199.99',
                'manage_stock' => true,
                'stock_quantity' => 10,
                'backorders' => 'no',
                'virtual' => false,
                'weight' => '1.2',
                'dimensions' => [
                    'length' => '10',
                    'width' => '5',
                    'height' => '2',
                ],
                'image' => [
                    'id' => 301,
                    'src' => 'https://example.test/media/variant-red.jpg',
                    'name' => 'Variant red',
                    'alt' => 'Variant red alt',
                ],
                'attributes' => [
                    [
                        'id' => 41,
                        'name' => 'Color',
                        'option' => 'Red',
                    ],
                    [
                        'id' => 42,
                        'name' => 'Memory',
                        'option' => '64GB',
                    ],
                ],
            ],
            [
                'id' => 1002,
                'parent_id' => 102,
                'status' => 'publish',
                'sku' => 'VP-BLUE-128',
                'price' => '299.99',
                'regular_price' => '299.99',
                'sale_price' => '',
                'manage_stock' => false,
                'stock_status' => 'instock',
                'backorders' => 'notify',
                'virtual' => false,
                'image' => [
                    'id' => 302,
                    'src' => 'https://example.test/media/variant-blue.jpg',
                    'name' => 'Variant blue',
                    'alt' => 'Variant blue alt',
                ],
                'attributes' => [
                    [
                        'id' => 41,
                        'name' => 'Color',
                        'option' => 'Blue',
                    ],
                    [
                        'id' => 42,
                        'name' => 'Memory',
                        'option' => '128GB',
                    ],
                ],
            ],
        ];

        Http::fakeSequence()
            ->push([$simpleProduct, $variableProduct], 200)
            ->push($variations, 200)
            ->push([], 200);

        $this->artisan('catalog:import-woocommerce')
            ->assertExitCode(0);

        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', [
            'slug' => 'simple-product',
            'status' => 'published',
        ]);

        $simple = Product::where('slug', 'simple-product')->firstOrFail();
        $this->assertEquals('Simple Product', $simple->name);
        $this->assertEquals('published', $simple->status);
        $this->assertEquals('Short description', $simple->excerpt);
        $this->assertCount(1, $simple->variants);

        $simpleVariant = $simple->variants->first();
        $this->assertEquals('SP-001', $simpleVariant->sku);
        $this->assertEquals(19.99, $simpleVariant->price);
        $this->assertEquals(5, $simpleVariant->inventory_quantity);
        $this->assertTrue($simpleVariant->is_default);

        $this->assertDatabaseHas('categories', [
            'slug' => 'category-a',
            'name' => 'Category A',
        ]);

        $this->assertDatabaseHas('category_product', [
            'product_id' => $simple->id,
            'is_primary' => 1,
        ]);

        $this->assertDatabaseHas('product_media', [
            'product_id' => $simple->id,
            'path' => 'https://example.test/media/simple.jpg',
            'is_primary' => 1,
        ]);

        $this->assertDatabaseHas('attributes', [
            'code' => 'material',
            'name' => 'Material',
        ]);

        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $simple->id,
            'value_text' => 'Aluminum',
        ]);

        $variable = Product::where('slug', 'variable-product')->firstOrFail();
        $this->assertEquals('Variable Product', $variable->name);
        $this->assertEquals('published', $variable->status);
        $this->assertCount(2, $variable->variants);
        $this->assertCount(2, $variable->options);

        $defaultVariant = $variable->variants->firstWhere('is_default', true);
        $this->assertNotNull($defaultVariant);
        $this->assertEquals('VP-RED-64', $defaultVariant->sku);
        $this->assertEquals(199.99, $defaultVariant->price);
        $this->assertEquals(10, $defaultVariant->inventory_quantity);

        $optionCodes = $variable->options->pluck('code')->all();
        $this->assertEqualsCanonicalizing(['pa-color', 'pa-memory'], $optionCodes);

        $colorOption = ProductOption::where('product_id', $variable->id)->where('code', 'pa-color')->first();
        $this->assertNotNull($colorOption);

        $colorValues = ProductOptionValue::where('product_option_id', $colorOption->id)
            ->pluck('value')
            ->all();
        $this->assertEqualsCanonicalizing(['Red', 'Blue'], $colorValues);

        $this->assertDatabaseHas('product_media', [
            'product_id' => $variable->id,
            'product_variant_id' => $defaultVariant->id,
            'path' => 'https://example.test/media/variant-red.jpg',
        ]);

        $totalMedia = ProductMedia::count();
        $this->assertEquals(4, $totalMedia);
    }
}
