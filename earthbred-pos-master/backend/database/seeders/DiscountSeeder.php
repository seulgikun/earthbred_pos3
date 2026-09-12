<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Discount;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        Discount::create(['name' => '10% Off', 'percentage' => 10]);
        Discount::create(['name' => '15% Off', 'percentage' => 15]);
        Discount::create(['name' => '20% Off', 'percentage' => 20]);
        Discount::create(['name' => 'Senior 30%', 'percentage' => 30]);
    }
}
