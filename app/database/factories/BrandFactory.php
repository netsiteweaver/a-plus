<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'website_url' => $this->faker->url(),
            'description' => $this->faker->sentence(12),
            'logo_url' => $this->faker->imageUrl(200, 200, 'business', true),
            'meta_title' => $name . ' Electronics',
            'meta_description' => $this->faker->sentence(16),
            'data' => [
                'founded' => $this->faker->year(),
                'hq' => $this->faker->city(),
            ],
            'is_active' => true,
        ];
    }
}
