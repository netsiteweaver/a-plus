<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
    }

    protected function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_cannot_access_admin_api(): void
    {
        $response = $this->getJson('/api/admin/brands');

        $response->assertUnauthorized();
    }

    public function test_user_without_permissions_gets_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/admin/brands');

        $response->assertForbidden();
    }

    public function test_admin_can_create_brand(): void
    {
        $admin = $this->actingAsAdmin();

        $payload = [
            'name' => 'Nimbus Labs',
            'slug' => 'nimbus-labs',
            'website_url' => 'https://nimbus.example.com',
            'description' => 'Cutting edge peripherals.',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->postJson('/api/admin/brands', $payload);

        $response->assertOk()->assertJsonPath('data.name', 'Nimbus Labs');
        $this->assertDatabaseHas('brands', ['slug' => 'nimbus-labs', 'is_active' => true]);
    }

    public function test_admin_can_create_and_update_product(): void
    {
        $admin = $this->actingAsAdmin();

        $brand = Brand::factory()->create();
        $categories = Category::factory()->count(2)->create();

        $createResponse = $this->actingAs($admin)->postJson('/api/admin/products', [
            'name' => 'Hyperbook Pro',
            'slug' => 'hyperbook-pro',
            'brand_id' => $brand->id,
            'status' => 'draft',
            'category_ids' => [$categories[0]->id, $categories[1]->id],
            'primary_category_id' => $categories[0]->id,
        ]);

        $createResponse->assertOk();
        $productId = $createResponse->json('data.id');

        $this->assertDatabaseHas('products', ['id' => $productId, 'status' => 'draft']);
        $this->assertDatabaseHas('category_product', [
            'product_id' => $productId,
            'category_id' => $categories[0]->id,
            'is_primary' => true,
        ]);

        $updateResponse = $this->actingAs($admin)->putJson("/api/admin/products/{$productId}", [
            'status' => 'published',
            'category_ids' => [$categories[1]->id],
            'primary_category_id' => $categories[1]->id,
        ]);

        $updateResponse->assertOk()->assertJsonPath('data.status', 'published');
        $this->assertDatabaseHas('products', ['id' => $productId, 'status' => 'published']);
        $this->assertDatabaseHas('category_product', [
            'product_id' => $productId,
            'category_id' => $categories[1]->id,
            'is_primary' => true,
        ]);
    }

    public function test_admin_can_manage_variants_and_options(): void
    {
        $admin = $this->actingAsAdmin();

        $product = Product::factory()->create();

        $optionResponse = $this->actingAs($admin)->postJson("/api/admin/products/{$product->id}/options", [
            'code' => 'color',
            'name' => 'Color',
            'input_type' => 'swatch',
            'is_required' => true,
            'position' => 1,
        ]);

        $optionResponse->assertOk();
        $optionId = $optionResponse->json('data.id');

        $valueResponse = $this->actingAs($admin)->postJson("/api/admin/products/{$product->id}/options/{$optionId}/values", [
            'value' => 'midnight',
            'display_value' => 'Midnight',
            'position' => 1,
        ]);

        $valueResponse->assertOk();
        $valueId = $valueResponse->json('data.id');

        $variantResponse = $this->actingAs($admin)->postJson("/api/admin/products/{$product->id}/variants", [
            'sku' => 'VAR-001',
            'status' => 'draft',
            'price' => 199.99,
            'inventory_quantity' => 5,
            'option_value_ids' => [$valueId],
        ]);

        $variantResponse->assertOk();
        $variantId = $variantResponse->json('data.id');

        $this->assertDatabaseHas('product_variants', ['id' => $variantId, 'product_id' => $product->id]);
        $this->assertDatabaseHas('product_variant_option_value', ['product_variant_id' => $variantId, 'product_option_value_id' => $valueId]);

        $setDefault = $this->actingAs($admin)->putJson("/api/admin/products/{$product->id}/variants/{$variantId}", [
            'is_default' => true,
        ]);

        $setDefault->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'default_variant_id' => $variantId]);
    }

    public function test_admin_can_assign_attribute_values(): void
    {
        $admin = $this->actingAsAdmin();

        $product = Product::factory()->create();
        $attribute = Attribute::factory()->create(['type' => 'select']);
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'm3-max',
        ]);

        $assignResponse = $this->actingAs($admin)->postJson("/api/admin/products/{$product->id}/attributes", [
            'attribute_id' => $attribute->id,
            'attribute_value_id' => $attributeValue->id,
        ]);

        $assignResponse->assertOk();
        $assignmentId = $assignResponse->json('data.id');

        $this->assertDatabaseHas('product_attribute_values', [
            'id' => $assignmentId,
            'product_id' => $product->id,
            'attribute_id' => $attribute->id,
            'attribute_value_id' => $attributeValue->id,
        ]);

        $updateResponse = $this->actingAs($admin)->putJson("/api/admin/products/{$product->id}/attributes/{$assignmentId}", [
            'value_text' => 'Apple M3 Max',
        ]);

        $updateResponse->assertOk();
        $this->assertDatabaseHas('product_attribute_values', ['id' => $assignmentId, 'value_text' => 'Apple M3 Max']);
    }
}
