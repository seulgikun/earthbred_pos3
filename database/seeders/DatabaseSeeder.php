<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'cashier@earthbred.com'],
            [
                'name' => 'Earthbred Cashier',
                'password' => bcrypt('123456'),
                'role' => 'cashier',
                'pin' => '123456',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'manager@earthbred.com'],
            [
                'name' => 'Juan Reyes',
                'password' => bcrypt('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'christopherlim1995@gmail.com'],
            [
                'name' => 'Christopher Lim',
                'password' => bcrypt('password'),
                'role' => 'owner',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ProductSeeder::class,
            AddonSeeder::class,
            DiscountSeeder::class,
            InventorySeeder::class,
        ]);

        \App\Models\ShiftNote::create([
            'note' => 'Ice machine making a loud noise since 9am, might need servicing.',
            'cashier_name' => 'Aries Maroliña',
            'category' => 'Equipment',
            'is_done' => true,
        ]);

        \App\Models\ShiftNote::create([
            'note' => 'Weekly inventory stock check of packaging items needs to be completed tonight.',
            'cashier_name' => 'Aries Maroliña',
            'category' => 'Task',
            'is_done' => false,
        ]);

        \App\Models\ShiftNote::create([
            'note' => 'Customer complained that the strawberry drink was too sweet.',
            'cashier_name' => 'Aries Maroliña',
            'category' => 'Complaint',
            'is_done' => false,
        ]);

        \App\Models\ShiftNote::create([
            'note' => 'Restocked all cups and lids under the counter for the next shift.',
            'cashier_name' => 'Aries Maroliña',
            'category' => 'General',
            'is_done' => true,
        ]);
    }
}
