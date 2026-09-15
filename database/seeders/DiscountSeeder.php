<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Discount;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        Discount::firstOrCreate(['name' => '10% Off'], ['percentage' => 10]);
        Discount::firstOrCreate(['name' => '15% Off'], ['percentage' => 15]);
        Discount::firstOrCreate(['name' => '20% Off'], ['percentage' => 20]);
        Discount::firstOrCreate(['name' => 'Senior 30%'], ['percentage' => 30]);
    }
}
