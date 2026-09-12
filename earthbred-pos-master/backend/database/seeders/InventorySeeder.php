<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\InventoryLog;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'item_name' => 'Whole Milk (L)',
                'category' => 'Ingredients',
                'quantity' => 8,
                'min_threshold' => 3,
                'latest_issue_type' => 'Restocked'
            ],
            [
                'item_name' => 'Oat Milk (L)',
                'category' => 'Ingredients',
                'quantity' => 3,
                'min_threshold' => 5,
                'latest_issue_type' => 'Morning Check'
            ],
            [
                'item_name' => 'Blueberry Syrup',
                'category' => 'Ingredients',
                'quantity' => 0,
                'min_threshold' => 2,
                'latest_issue_type' => 'Expired'
            ],
            [
                'item_name' => 'Caramel Syrup',
                'category' => 'Ingredients',
                'quantity' => 0,
                'min_threshold' => 2,
                'latest_issue_type' => 'Restocked'
            ],
            [
                'item_name' => 'Espresso Beans (kg)',
                'category' => 'Ingredients',
                'quantity' => 6,
                'min_threshold' => 2,
                'latest_issue_type' => 'Morning Check'
            ],
            [
                'item_name' => 'Vanilla Syrup',
                'category' => 'Ingredients',
                'quantity' => 4,
                'min_threshold' => 2,
                'latest_issue_type' => 'Restocked'
            ],
            [
                'item_name' => 'Matcha Powder (g)',
                'category' => 'Ingredients',
                'quantity' => 200,
                'min_threshold' => 50,
                'latest_issue_type' => 'Restocked'
            ],
            [
                'item_name' => 'Paper Cups 12oz (pcs)',
                'category' => 'Packaging',
                'quantity' => 20,
                'min_threshold' => 25,
                'latest_issue_type' => 'Morning Check'
            ],
            [
                'item_name' => 'Paper Cups 8oz (pcs)',
                'category' => 'Packaging',
                'quantity' => 60,
                'min_threshold' => 25,
                'latest_issue_type' => 'Restocked'
            ]
        ];

        foreach ($items as $item) {
            $inventory = Inventory::create($item);

            // Log the initial quantity setup
            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'quantity_changed' => $item['quantity'],
                'issue_type' => $item['latest_issue_type'],
                'notes' => 'Initial stock setup'
            ]);
        }
    }
}
