<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'sku' => fake()->unique()->optional()->bothify('SKU-####'),
            'price' => fake()->optional()->randomFloat(2, 1, 5000),
            'category' => fake()->optional()->word(),
            'brand' => fake()->optional()->company(),
            'unit' => fake()->optional()->randomElement(['pcs', 'box', 'kg', 'ltr']),
            'stock_quantity' => fake()->numberBetween(0, 1000),
            'meta' => [
                'color' => fake()->safeColorName(),
            ],
            'is_active' => true,
            'created_by' => null,
        ];
    }
}
