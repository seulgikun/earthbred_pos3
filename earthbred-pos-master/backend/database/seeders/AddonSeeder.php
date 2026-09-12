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
            ['name' => 'Extra Espresso Shot', 'price' => 30.00],
            ['name' => 'Oat Milk', 'price' => 40.00],
            ['name' => 'Less Ice', 'price' => 0.00],
            ['name' => 'Extra Sweet', 'price' => 20.00],
        ];

        foreach ($addons as $addon) {
            Addon::firstOrCreate(['name' => $addon['name']], $addon);
        }
    }
}
