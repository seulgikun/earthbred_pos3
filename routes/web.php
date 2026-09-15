<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ShiftNoteController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\AddonController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Authentication & Reset Routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('index');
})->name('login');

Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->name('verification.verify');
Route::get('/reset-password', function () {
    return view('reset-password');
});

/*
|--------------------------------------------------------------------------
| POS & Cashier Operations (Cashier, Manager, Owner)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:cashier,manager,owner'])->group(function () {
    Route::get('/pos', function () {
        $products = \Illuminate\Support\Facades\Cache::remember('pos_products', 300, function() {
            return \App\Models\Product::all();
        });
        $addons = \Illuminate\Support\Facades\Cache::remember('pos_addons', 300, function() {
            return \App\Models\Addon::all();
        });
        $inventories = \App\Models\Inventory::with('categoryRecord')->get(['id', 'item_name', 'category_id', 'quantity', 'min_threshold']);
        return view('pos', compact('products', 'addons', 'inventories'));
    });

    Route::get('/checkout', function () {
        $discounts = \Illuminate\Support\Facades\Cache::remember('checkout_discounts', 300, function() {
            return \App\Models\Discount::all();
        });
        return view('checkout', compact('discounts'));
    });

    Route::post('/checkout', [OrderController::class, 'store']);
    Route::get('/queue', [OrderController::class, 'index']);
    Route::get('/api/queue/live', [OrderController::class, 'liveQueue']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    Route::get('/shift-notes', [ShiftNoteController::class, 'index']);
    Route::post('/shift-notes', [ShiftNoteController::class, 'store']);
    Route::patch('/shift-notes/{id}/done', [ShiftNoteController::class, 'markDone']);

    Route::get('/inventory', function () {
        return view('inventory');
    });

    Route::get('/api/inventory', [InventoryController::class, 'index']);
    Route::get('/api/inventory/archived', [InventoryController::class, 'getArchived']);
    Route::post('/api/inventory', [InventoryController::class, 'storeItem']);
    Route::post('/api/inventory/{id}/add', [InventoryController::class, 'addStock']);
    Route::post('/api/inventory/{id}/edit', [InventoryController::class, 'editStock']);
    Route::post('/api/inventory/{id}/restore', [InventoryController::class, 'restoreItem']);
    Route::delete('/api/inventory/{id}', [InventoryController::class, 'destroy']);
    Route::get('/api/inventory/logs', [InventoryController::class, 'getLogs']);

    // POS real-time stock status — returns out-of-stock product IDs + low stock alerts
    Route::get('/api/pos/stock-status', function () {
        $inventories = \App\Models\Inventory::with('categoryRecord')->get(['id', 'item_name', 'category_id', 'quantity', 'min_threshold']);
        $products = \App\Models\Product::all();
        $outOfStockIds = [];
        foreach ($products as $product) {
            if ($product->isOutOfStock($inventories)) {
                $outOfStockIds[] = $product->id;
            }
        }
        // Low stock alerts: items at or below min threshold
        $lowStockAlerts = $inventories->filter(fn($i) => $i->quantity > 0 && $i->quantity <= $i->min_threshold)
            ->map(fn($i) => ['item_name' => $i->item_name, 'quantity' => $i->quantity, 'min_threshold' => $i->min_threshold])
            ->values()->toArray();
        // Out of stock items (for banner)
        $outOfStockAlerts = $inventories->filter(fn($i) => $i->quantity <= 0)
            ->map(fn($i) => ['item_name' => $i->item_name])
            ->values()->toArray();
        return response()->json([
            'out_of_stock' => $outOfStockIds,
            'low_stock_alerts' => $lowStockAlerts,
            'out_of_stock_alerts' => $outOfStockAlerts,
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Manager & Owner Operational Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['role:manager,owner'])->group(function () {
    Route::get('/manager', [ManagerController::class, 'index']);
    Route::get('/manager/inventory', function () {
        return view('inventory', ['isManager' => true]);
    });
    Route::get('/manager/shift-notes', [ManagerController::class, 'shiftNotes']);
    Route::get('/manager/sales-report', [ManagerController::class, 'salesReport']);
    Route::get('/manager/ai', function () {
        return view('manager-ai');
    });
    Route::get('/manager/products', [ProductController::class, 'index']);

    Route::post('/api/manager/ai/chat', [AiController::class, 'chat'])->middleware('throttle:15,1');
    Route::get('/api/manager/stats', [ManagerController::class, 'getStats']);
    Route::get('/api/manager/sales-data', [ManagerController::class, 'getSalesData']);
    Route::get('/api/manager/cashier-sales', [ManagerController::class, 'getCashierSales']);
    Route::get('/api/manager/cashier-sales/{id}/details', [ManagerController::class, 'getCashierDetails']);
    Route::get('/api/manager/peak-hours', [ManagerController::class, 'getPeakHoursData']);
    Route::get('/api/manager/shift-summary', [ManagerController::class, 'getShiftSummary']);

    Route::post('/api/products', [ProductController::class, 'store']);
    Route::post('/api/products/{id}', [ProductController::class, 'update']);
    Route::delete('/api/products/{id}', [ProductController::class, 'destroy']);

    Route::get('/api/discounts', [DiscountController::class, 'index']);
    Route::post('/api/discounts', [DiscountController::class, 'store']);
    Route::delete('/api/discounts/{id}', [DiscountController::class, 'destroy']);

    Route::get('/api/addons', [AddonController::class, 'index']);
    Route::post('/api/addons', [AddonController::class, 'store']);
    Route::post('/api/addons/{id}', [AddonController::class, 'update']);
    Route::delete('/api/addons/{id}', [AddonController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Owner Exclusive Administration Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['role:owner'])->group(function () {
    Route::get('/manager/accounts', function () {
        return view('manager-accounts');
    });
});
