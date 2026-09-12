<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            ['name' => 'Americano', 'category' => 'coffee', 'price' => 95, 'picture' => 'americano.png'],
            ['name' => 'Cafe Latte', 'category' => 'coffee', 'price' => 95, 'picture' => 'americano.png'],
            ['name' => 'Cafe Mocha', 'category' => 'coffee', 'price' => 89, 'picture' => 'americano.png'],
            ['name' => 'Matcha Drink', 'category' => 'non-coffee', 'price' => 99, 'picture' => 'strawberry.png'],
            ['name' => 'Sweetened Lemonade', 'category' => 'lemonade', 'price' => 95, 'picture' => 'strawberry.png'],
            ['name' => 'Strawberry Drink', 'category' => 'lemonade', 'price' => 95, 'picture' => 'strawberry.png'],
            ['name' => 'Strawberry Lemonade', 'category' => 'lemonade', 'price' => 109, 'picture' => 'strawberry.png'],
            ['name' => 'Chicken Ala King', 'category' => 'foods', 'price' => 109, 'picture' => 'rice_bowl.png'],
            ['name' => 'Sweet Garlic Longganisa', 'category' => 'foods', 'price' => 95, 'picture' => 'rice_bowl.png'],
            ['name' => 'Chicken Fried Rice', 'category' => 'foods', 'price' => 130, 'picture' => 'rice_bowl.png'],
            ['name' => 'Cheezy Bacon', 'category' => 'foods', 'price' => 130, 'picture' => 'rice_bowl.png'],
            ['name' => 'Beef Tapa', 'category' => 'foods', 'price' => 135, 'picture' => 'rice_bowl.png'],
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}
