<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::query()->inRandomOrder()->value('id') ?? Attribute::factory(),
            'value' => $this->faker->unique()->word(),
            'display_value' => $this->faker->words(2, true),
            'numeric_value' => null,
            'data' => [],
        ];
    }
}
