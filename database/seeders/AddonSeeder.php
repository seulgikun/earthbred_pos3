<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Addon;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $addons = [
            ['name' => 'Extra Rice', 'price' => 20.00, 'category' => 'food'],
            ['name' => 'Extra Egg', 'price' => 15.00, 'category' => 'food'],
            ['name' => 'Extra Espresso Shot', 'price' => 30.00, 'category' => 'drinks'],
            ['name' => 'Oat Milk', 'price' => 40.00, 'category' => 'drinks'],
            ['name' => 'Less Ice', 'price' => 0.00, 'category' => 'drinks'],
            ['name' => 'Extra Sweet', 'price' => 20.00, 'category' => 'drinks'],
        ];

        foreach ($addons as $addon) {
            Addon::updateOrCreate(['name' => $addon['name']], $addon);
        }
    }
}
