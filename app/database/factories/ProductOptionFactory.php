<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductOption>
 */
class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::query()->inRandomOrder()->value('id') ?? Product::factory(),
            'code' => $this->faker->unique()->lexify('option-????'),
            'name' => $this->faker->words(2, true),
            'input_type' => 'select',
            'is_required' => true,
            'position' => $this->faker->numberBetween(1, 5),
            'data' => [],
        ];
    }
}
