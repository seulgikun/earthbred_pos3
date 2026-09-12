<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class OrderSampleSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['name' => 'Americano', 'price' => 95, 'category' => 'coffee'],
            ['name' => 'Cafe Latte', 'price' => 95, 'category' => 'coffee'],
            ['name' => 'Cafe Mocha', 'price' => 89, 'category' => 'coffee'],
            ['name' => 'Matcha Drink', 'price' => 99, 'category' => 'non-coffee'],
            ['name' => 'Sweetened', 'price' => 95, 'category' => 'lemonade'],
            ['name' => 'Strawberry Drink', 'price' => 95, 'category' => 'lemonade'],
            ['name' => 'Strawberry', 'price' => 109, 'category' => 'lemonade'],
            ['name' => 'Chicken Ala King', 'price' => 109, 'category' => 'rice-bowls'],
            ['name' => 'Sweet Garlic Longganisa', 'price' => 95, 'category' => 'rice-bowls'],
            ['name' => 'Chicken Fried Rice', 'price' => 130, 'category' => 'rice-bowls'],
            ['name' => 'Cheezy Bacon', 'price' => 130, 'category' => 'rice-bowls'],
            ['name' => 'Beef Tapa', 'price' => 135, 'category' => 'rice-bowls'],
        ];

        $addonsList = [
            ['name' => 'Extra Espresso Shot', 'price' => 30],
            ['name' => 'Oat Milk', 'price' => 40],
            ['name' => 'Extra Sweet', 'price' => 20],
            ['name' => 'Less Ice', 'price' => 0]
        ];

        $now = Carbon::now();

        // Let's generate orders for the last 30 days (including today)
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // On weekends (Sat/Sun) let's have more sales.
            $isWeekend = $date->isWeekend();
            $numOrders = $isWeekend ? rand(15, 25) : rand(10, 18);

            for ($j = 0; $j < $numOrders; $j++) {
                // Set transaction time randomly between 7 AM and 9 PM
                $orderTime = $date->copy()->setHour(rand(7, 20))->setMinute(rand(0, 59))->setSecond(rand(0, 59));
                
                // Keep it in the past, don't exceed current time if it's today
                if ($i === 0 && $orderTime->gt($now)) {
                    $orderTime = $now->copy()->subMinutes(rand(5, 60));
                }

                $numItems = rand(1, 3);
                $orderItemsData = [];
                $subtotal = 0;

                for ($k = 0; $k < $numItems; $k++) {
                    $prod = $products[array_rand($products)];
                    $qty = rand(1, 2);
                    
                    // Addons (beverages only, no add-ons on food)
                    $addons = [];
                    $addonsTotal = 0;
                    $prodCat = strtolower(trim($prod['category'] ?? ''));
                    $isFoodItem = in_array($prodCat, ['foods', 'food']) || strpos($prodCat, 'food') !== false;

                    if (!$isFoodItem && rand(0, 100) < 40) { // 40% chance of add-on for drinks
                        $addon = $addonsList[array_rand($addonsList)];
                        $addons[] = $addon['name'];
                        $addonsTotal += $addon['price'] * $qty;
                    }

                    $itemTotal = ($prod['price'] * $qty) + $addonsTotal;
                    $subtotal += $itemTotal;

                    $orderItemsData[] = [
                        'customer_name' => rand(0, 100) < 50 ? $this->getRandomCustomerName() : null,
                        'product_name' => $prod['name'],
                        'price' => $prod['price'],
                        'quantity' => $qty,
                        'addons' => $addons,
                        'addons_total' => $addonsTotal,
                        'item_total' => $itemTotal,
                        'created_at' => $orderTime,
                        'updated_at' => $orderTime
                    ];
                }

                // Discount logic (15% chance of discount)
                $discountPercent = 0;
                $discountAmount = 0;
                if (rand(0, 100) < 15) {
                    $discountPercent = array_rand([5 => 5, 10 => 10, 20 => 20]);
                    $discountAmount = round(($subtotal * $discountPercent) / 100, 2);
                }

                $total = $subtotal - $discountAmount;
                $paymentMethod = rand(0, 100) < 65 ? 'cash' : 'gcash';
                
                // Status distribution: 90% completed, 8% pending, 2% void
                $statusRoll = rand(1, 100);
                $status = 'completed';
                if ($statusRoll > 90) {
                    $status = 'pending';
                } elseif ($statusRoll > 98) {
                    $status = 'void';
                }

                // Save Order
                $order = Order::create([
                    'subtotal' => $subtotal,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'status' => $status,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime
                ]);

                // Save items
                foreach ($orderItemsData as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }
            }
        }
    }

    private function getRandomCustomerName()
    {
        $names = ['Alex', 'John', 'Sarah', 'Maria', 'Dino', 'Aries', 'Erika', 'Mark', 'Paul', 'Rhea', 'Jane', 'Kevin', 'Ken', 'Vince'];
        return $names[array_rand($names)];
    }
}
