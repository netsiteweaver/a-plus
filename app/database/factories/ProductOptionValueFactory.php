<?php

namespace Database\Factories;

use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductOptionValue>
 */
class ProductOptionValueFactory extends Factory
{
    protected $model = ProductOptionValue::class;

    public function definition(): array
    {
        return [
            'product_option_id' => ProductOption::query()->inRandomOrder()->value('id') ?? ProductOption::factory(),
            'value' => $this->faker->unique()->lexify('value-????'),
            'display_value' => $this->faker->words(2, true),
            'hex_value' => sprintf('#%06X', $this->faker->numberBetween(0, 0xFFFFFF)),
            'thumbnail_url' => $this->faker->imageUrl(200, 200, 'technology', true),
            'position' => $this->faker->numberBetween(1, 5),
            'data' => [],
        ];
    }
}
