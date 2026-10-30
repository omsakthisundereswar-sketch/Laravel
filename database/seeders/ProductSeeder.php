<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'unique_code' => 'PRD-1001',
                'name' => 'Colgate Toothpaste',
                'price' => 50,
                'tax_percentage' => 8,
                'stock_quantity' => 25,
            ],
            [
                'unique_code' => 'PRD-1002',
                'name' => 'Parle-G Biscuit',
                'price' => 10,
                'tax_percentage' => 8,
                'stock_quantity' => 30,
            ],
            [
                'unique_code' => 'PRD-1003',
                'name' => 'Bread',
                'price' => 35,
                'tax_percentage' => 5,
                'stock_quantity' => 4,
            ],
            [
                'unique_code' => 'PRD-1004',
                'name' => 'Milk 1L',
                'price' => 60,
                'tax_percentage' => 5,
                'stock_quantity' => 9,
            ],
            [
                'unique_code' => 'PRD-1005',
                'name' => 'Eggs (12)',
                'price' => 72,
                'tax_percentage' => 5,
                'stock_quantity' => 2,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['unique_code' => $product['unique_code']],
                $product
            );
        }
    }
}
