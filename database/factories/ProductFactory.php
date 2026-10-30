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
            'name' => fake()->randomElement([
                'Wireless Mouse',
                'USB Keyboard',
                'Monitor 24',
                'Office Chair',
                'Desk Lamp',
                'Bluetooth Speaker',
                'Laptop Stand',
                'Mechanical Keyboard',
                'Webcam HD',
                'USB-C Hub',
            ]),
            'unique_code' => 'PRD-' . fake()->unique()->numerify('####'),
            'price' => fake()->randomFloat(2, 25, 500),
            'tax_percentage' => fake()->randomFloat(2, 5, 18),
            'stock_quantity' => fake()->numberBetween(0, 50),
        ];
    }
}
