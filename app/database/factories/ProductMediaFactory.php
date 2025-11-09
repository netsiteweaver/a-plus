<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductMedia>
 */
class ProductMediaFactory extends Factory
{
    protected $model = ProductMedia::class;

    public function definition(): array
    {
        $imageUrl = $this->faker->imageUrl(1600, 900, 'technology', true);
        
        return [
            'product_id' => Product::query()->inRandomOrder()->value('id') ?? Product::factory(),
            'product_variant_id' => null,
            'type' => 'image',
            'disk' => 'remote',
            'path' => $imageUrl,
            'is_primary' => false,
            'position' => $this->faker->numberBetween(0, 5),
            'alt_text' => $this->faker->sentence(6),
            'caption' => $this->faker->sentence(8),
            'data' => [],
        ];
    }
}
