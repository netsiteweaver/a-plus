<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::query()->inRandomOrder()->value('id') ?? Product::factory(),
            'sku' => strtoupper(Str::random(12)),
            'barcode' => $this->faker->ean13(),
            'status' => 'published',
            'price' => $this->faker->numberBetween(999, 2999),
            'compare_at_price' => $this->faker->boolean(40) ? $this->faker->numberBetween(1199, 3299) : null,
            'cost' => $this->faker->numberBetween(600, 1500),
            'currency' => 'USD',
            'inventory_sku' => strtoupper(Str::random(10)),
            'inventory_policy' => 'deny',
            'inventory_quantity' => $this->faker->numberBetween(10, 120),
            'track_inventory' => true,
            'weight' => $this->faker->randomFloat(2, 1.2, 3.8),
            'weight_unit' => 'kg',
            'length' => $this->faker->randomFloat(2, 12, 18),
            'width' => $this->faker->randomFloat(2, 8, 14),
            'height' => $this->faker->randomFloat(2, 0.6, 1.2),
            'dimension_unit' => 'inch',
            'is_default' => false,
            'requires_shipping' => true,
            'requires_serial' => false,
            'published_at' => now()->subDays($this->faker->numberBetween(1, 90)),
            'data' => [],
        ];
    }
}
