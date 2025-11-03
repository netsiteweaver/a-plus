<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductAttributeValue>
 */
class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

    public function definition(): array
    {
        $attributeId = Attribute::query()->inRandomOrder()->value('id') ?? Attribute::factory();
        $attributeValueId = AttributeValue::query()->where('attribute_id', $attributeId)->inRandomOrder()->value('id');

        return [
            'product_id' => Product::query()->inRandomOrder()->value('id') ?? Product::factory(),
            'attribute_id' => $attributeId,
            'attribute_value_id' => $attributeValueId,
            'value_text' => $attributeValueId ? null : $this->faker->sentence(3),
            'value_number' => null,
            'value_json' => null,
        ];
    }
}
