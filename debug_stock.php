<?php
// Test the isOutOfStock logic directly without Laravel

$inventoryItems = [
    ['item_name'=>'Whole Milk (L)',       'quantity'=>0,   'category'=>'Ingredients'],
    ['item_name'=>'Oat Milk (L)',          'quantity'=>3,   'category'=>'Ingredients'],
    ['item_name'=>'Blueberry Syrup',       'quantity'=>0,   'category'=>'Ingredients'],
    ['item_name'=>'Caramel Syrup',         'quantity'=>0,   'category'=>'Ingredients'],
    ['item_name'=>'Espresso Beans (kg)',   'quantity'=>11,  'category'=>'Ingredients'],
    ['item_name'=>'Vanilla Syrup',         'quantity'=>20,  'category'=>'Ingredients'],
    ['item_name'=>'Matcha Powder (g)',     'quantity'=>200, 'category'=>'Ingredients'],
    ['item_name'=>'Paper Cups 12oz (pcs)','quantity'=>20,  'category'=>'Packaging'],
    ['item_name'=>'Paper Cups 8oz (pcs)', 'quantity'=>63,  'category'=>'Packaging'],
    ['item_name'=>'coffee beans',          'quantity'=>0,   'category'=>'Coffee Beans'],
];

$products = [
    ['name'=>'Americano',         'category'=>'coffee'],
    ['name'=>'Cafe Latte',        'category'=>'coffee'],
    ['name'=>'Cafe Mocha',        'category'=>'coffee'],
    ['name'=>'Matcha Drink',      'category'=>'non-coffee'],
    ['name'=>'Strawberry Drink',  'category'=>'non-coffee'],
    ['name'=>'Sweetened Lemonade','category'=>'lemonade'],
];

echo "=== TESTING isOutOfStock logic ===\n\n";

foreach ($products as $prod) {
    $prodName = strtolower(trim($prod['name']));
    $prodCat  = strtolower(trim($prod['category']));
    $outOfStock = false;

    // 1. Direct item match
    foreach ($inventoryItems as $inv) {
        $invName = strtolower(trim($inv['item_name']));
        if (($invName === $prodName || str_contains($invName, $prodName) || str_contains($prodName, $invName)) && $inv['quantity'] <= 0) {
            $outOfStock = true;
            echo "  [MATCH-1 DIRECT] {$prod['name']}: matched '{$inv['item_name']}' qty={$inv['quantity']}\n";
            break;
        }
    }

    // 2. Coffee beans check
    if (!$outOfStock && ($prodCat === 'coffee' || str_contains($prodName, 'americano') || str_contains($prodName, 'latte') || str_contains($prodName, 'mocha'))) {
        $coffeeBeans = array_filter($inventoryItems, function($i) {
            $name = strtolower($i['item_name']);
            return str_contains($name, 'espresso bean') || str_contains($name, 'coffee bean') || (str_contains($name, 'bean') && !str_contains($name, 'jelly'));
        });
        echo "  [COFFEE-CHECK] {$prod['name']}: found ".count($coffeeBeans)." bean items\n";
        foreach ($coffeeBeans as $b) {
            echo "    Bean: '{$b['item_name']}' qty={$b['quantity']}\n";
        }
        if (!empty($coffeeBeans) && count(array_filter($coffeeBeans, fn($b) => $b['quantity'] <= 0)) === count($coffeeBeans)) {
            $outOfStock = true;
            echo "  => ALL beans out of stock!\n";
        } else {
            echo "  => Some beans still in stock — product available\n";
        }
    }

    $status = $outOfStock ? "OUT OF STOCK" : "IN STOCK";
    echo "RESULT: {$prod['name']} => $status\n\n";
}
