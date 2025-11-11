<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportWooCommerceProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_products_from_woocommerce(): void
    {
        $this->setupWooCommerceConfig();

        Http::fake([
            'https://woocommerce.test/wp-json/wc/v3/products/categories*' => Http::response([
                [
                    'id' => 22,
                    'name' => 'Laptops',
                    'slug' => 'laptops',
                    'parent' => 0,
                    'description' => 'High-end laptops',
                    'display' => 'visible',
                    'menu_order' => 0,
                ],
            ], 200, ['X-WP-TotalPages' => 1]),
            'https://woocommerce.test/wp-json/wc/v3/products/555/variations*' => Http::response([
                [
                    'id' => 7771,
                    'sku' => 'PRO-SILVER',
                    'status' => 'publish',
                    'price' => '1999',
                    'regular_price' => '1999',
                    'sale_price' => '',
                    'stock_quantity' => 5,
                    'manage_stock' => true,
                    'backorders' => 'no',
                    'stock_status' => 'instock',
                    'weight' => '2.0',
                    'dimensions' => ['length' => '30', 'width' => '20', 'height' => '2'],
                    'shipping_required' => true,
                    'date_created_gmt' => '2024-11-01T10:00:00',
                    'attributes' => [
                        ['id' => 9, 'name' => 'Color', 'option' => 'Silver'],
                    ],
                    'image' => ['id' => 9001, 'src' => 'https://cdn.test/img/pro-laptop-silver.jpg', 'name' => 'Silver', 'alt' => 'Silver variant'],
                ],
                [
                    'id' => 7772,
                    'sku' => 'PRO-GRAY',
                    'status' => 'publish',
                    'price' => '2099',
                    'regular_price' => '2099',
                    'sale_price' => '1999',
                    'stock_quantity' => 3,
                    'manage_stock' => true,
                    'backorders' => 'no',
                    'stock_status' => 'instock',
                    'weight' => '2.0',
                    'dimensions' => ['length' => '30', 'width' => '20', 'height' => '2'],
                    'shipping_required' => true,
                    'date_created_gmt' => '2024-11-02T10:00:00',
                    'attributes' => [
                        ['id' => 9, 'name' => 'Color', 'option' => 'Space Gray'],
                    ],
                    'image' => ['id' => 9002, 'src' => 'https://cdn.test/img/pro-laptop-gray.jpg', 'name' => 'Space Gray', 'alt' => 'Gray variant'],
                ],
            ], 200, ['X-WP-TotalPages' => 1]),
            'https://woocommerce.test/wp-json/wc/v3/products*' => Http::response([
                [
                    'id' => 555,
                    'name' => 'Pro Laptop',
                    'slug' => 'pro-laptop',
                    'type' => 'variable',
                    'status' => 'publish',
                    'permalink' => 'https://woocommerce.test/product/pro-laptop',
                    'date_created_gmt' => '2024-11-01T10:00:00',
                    'date_modified_gmt' => '2024-11-05T11:00:00',
                    'sku' => '',
                    'short_description' => '<p>Portable power</p>',
                    'description' => '<p>Full technical specification</p>',
                    'categories' => [
                        ['id' => 22, 'name' => 'Laptops', 'slug' => 'laptops'],
                    ],
                    'images' => [
                        ['id' => 800, 'src' => 'https://cdn.test/img/pro-laptop.jpg', 'name' => 'Pro Laptop', 'alt' => 'Product cover'],
                    ],
                    'attributes' => [
                        ['id' => 9, 'name' => 'Color', 'slug' => 'pa_color', 'position' => 0, 'visible' => true, 'variation' => true, 'options' => ['Silver', 'Space Gray']],
                        ['id' => 0, 'name' => 'Processor', 'slug' => 'processor', 'position' => 1, 'visible' => true, 'variation' => false, 'options' => ['M3']],
                    ],
                    'default_attributes' => [
                        ['id' => 9, 'name' => 'Color', 'option' => 'Silver'],
                    ],
                ],
            ], 200, ['X-WP-TotalPages' => 1]),
        ]);

        Artisan::call('catalog:import-woocommerce');

        $category = Category::firstWhere('slug', 'laptops');
        $this->assertNotNull($category);
        $this->assertEquals('Laptops', $category->name);
        $this->assertEquals('published', $category->status);
        $this->assertEquals(22, (int) data_get($category->data, 'woocommerce_id'));

        $product = Product::firstWhere('slug', 'pro-laptop');
        $this->assertNotNull($product);
        $this->assertEquals('configurable', $product->type);
        $this->assertEquals('published', $product->status);
        $this->assertEquals('Portable power', $product->excerpt);
        $this->assertEquals('M3', $product->specifications['Processor'] ?? null);
        $this->assertEquals(555, (int) data_get($product->data, 'woocommerce_id'));
        $this->assertTrue($product->categories()->where('slug', 'laptops')->exists());

        $this->assertCount(1, $product->options);
        $option = $product->options->first();
        $this->assertEquals('Color', $option->name);
        $this->assertCount(2, $option->values);

        $variants = ProductVariant::all();
        $this->assertCount(2, $variants);
        $defaultVariant = $variants->firstWhere('is_default', true);
        $this->assertNotNull($defaultVariant);
        $this->assertEquals('PRO-SILVER', $defaultVariant->sku);
        $this->assertEquals(7771, (int) data_get($defaultVariant->data, 'woocommerce_id'));
        $this->assertEquals($defaultVariant->id, $product->default_variant_id);

        $this->assertGreaterThan(0, $product->media()->count());
    }

    public function test_dry_run_does_not_persist_changes(): void
    {
        $this->setupWooCommerceConfig();

        Http::fake([
            '*' => Http::response([], 200, ['X-WP-TotalPages' => 1]),
        ]);

        Artisan::call('catalog:import-woocommerce', ['--dry-run' => true]);

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 0);
    }

    protected function setupWooCommerceConfig(): void
    {
        config()->set('woocommerce.url', 'https://woocommerce.test');
        config()->set('woocommerce.consumer_key', 'ck_test');
        config()->set('woocommerce.consumer_secret', 'cs_test');
        config()->set('woocommerce.verify', true);
        config()->set('woocommerce.default_currency', 'USD');
        config()->set('woocommerce.per_page', 50);
    }
}
