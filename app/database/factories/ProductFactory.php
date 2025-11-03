<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true) . ' ' . $this->faker->randomElement(['Pro', 'Ultra', 'X', 'Studio']);
        $slug = Str::slug($name . '-' . $this->faker->unique()->numberBetween(100, 999));

        return [
            'uuid' => (string) Str::uuid(),
            'slug' => $slug,
            'type' => 'standard',
            'brand_id' => Brand::query()->inRandomOrder()->value('id') ?? Brand::factory(),
            'name' => Str::title($name),
            'subtitle' => $this->faker->sentence(6),
            'sku' => strtoupper(Str::random(10)),
            'excerpt' => $this->faker->sentence(18),
            'description' => $this->faker->paragraphs(4, true),
            'specifications' => null,
            'data' => [
                'rating' => $this->faker->randomFloat(1, 4.2, 4.9),
                'rating_count' => $this->faker->numberBetween(25, 420),
                'badge' => $this->faker->randomElement(['new arrival', 'best seller', 'bundle & save']),
            ],
            'status' => 'published',
            'published_at' => now()->subDays($this->faker->numberBetween(1, 120)),
            'meta_title' => Str::title($name) . ' | ' . config('app.name'),
            'meta_description' => $this->faker->sentence(20),
        ];
    }
}
