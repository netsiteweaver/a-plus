<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'type' => 'catalog',
            'description' => $this->faker->paragraph(),
            'image_url' => $this->faker->imageUrl(1200, 800, 'technology', true),
            'status' => 'published',
            'position' => $this->faker->numberBetween(0, 100),
            'is_visible' => true,
            'is_featured' => $this->faker->boolean(30), // 30% chance of being featured
            'meta_title' => Str::title($name) . ' | ' . config('app.name'),
            'meta_description' => $this->faker->sentence(16),
            'data' => [
                'accent' => $this->faker->words(2, true),
            ],
        ];
    }
}
