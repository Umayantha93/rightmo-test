<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports', 'Toys'];
        
        return [
            'name' => fake()->unique()->words(3, true) . ' ' . fake()->numberBetween(100, 999),
            'category' => fake()->randomElement($categories),
            'price' => fake()->randomFloat(2, 10, 1000),
            'rating' => fake()->randomFloat(2, 1, 5),
            'image' => 'products/default.jpg',
            'description' => fake()->paragraph(),
        ];
    }
}
