<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        $code = Str::slug($this->faker->unique()->words(2, true), '_');

        return [
            'code' => $code,
            'name' => Str::title(str_replace('_', ' ', $code)),
            'type' => $this->faker->randomElement(['text', 'select', 'number']),
            'unit' => null,
            'is_filterable' => true,
            'is_required' => false,
            'data' => [],
        ];
    }
}
