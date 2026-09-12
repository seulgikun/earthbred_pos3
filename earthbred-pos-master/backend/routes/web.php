<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('index');
});

use App\Http\Controllers\UserController;
Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->name('verification.verify');
Route::get('/reset-password', function () {
    return view('reset-password');
});

Route::get('/pos', function () {
    $products = \Illuminate\Support\Facades\Cache::remember('pos_products', 300, function() {
        return \App\Models\Product::all();
    });
    $addons = \Illuminate\Support\Facades\Cache::remember('pos_addons', 300, function() {
        return \App\Models\Addon::all();
    });
    return view('pos', compact('products', 'addons'));
});

Route::get('/checkout', function () {
    $discounts = \Illuminate\Support\Facades\Cache::remember('checkout_discounts', 300, function() {
        return \App\Models\Discount::all();
    });
    return view('checkout', compact('discounts'));
});

Route::post('/checkout', [OrderController::class, 'store']);

Route::get('/queue', [OrderController::class, 'index']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

use App\Http\Controllers\ShiftNoteController;

Route::get('/shift-notes', [ShiftNoteController::class, 'index']);
Route::post('/shift-notes', [ShiftNoteController::class, 'store']);
Route::patch('/shift-notes/{id}/done', [ShiftNoteController::class, 'markDone']);

use App\Http\Controllers\ManagerController;
use App\Http\Controllers\InventoryController;

Route::get('/inventory', function () {
    return view('inventory');
});

Route::get('/manager/inventory', function () {
    return view('inventory', ['isManager' => true]);
});

Route::get('/manager', [ManagerController::class, 'index']);
Route::get('/manager/shift-notes', [ManagerController::class, 'shiftNotes']);
Route::get('/manager/sales-report', [ManagerController::class, 'salesReport']);
Route::get('/manager/ai', function () {
    return view('manager-ai');
});
Route::get('/manager/accounts', function () {
    return view('manager-accounts');
});
use App\Http\Controllers\AiController;
Route::post('/api/manager/ai/chat', [AiController::class, 'chat'])->middleware('throttle:15,1');

Route::get('/api/manager/stats', [ManagerController::class, 'getStats']);
Route::get('/api/manager/sales-data', [ManagerController::class, 'getSalesData']);
Route::get('/api/manager/cashier-sales', [ManagerController::class, 'getCashierSales']);
Route::get('/api/manager/cashier-sales/{id}/details', [ManagerController::class, 'getCashierDetails']);

Route::get('/api/inventory', [InventoryController::class, 'index']);
Route::post('/api/inventory', [InventoryController::class, 'storeItem']);
Route::post('/api/inventory/{id}/add', [InventoryController::class, 'addStock']);
Route::post('/api/inventory/{id}/edit', [InventoryController::class, 'editStock']);
Route::delete('/api/inventory/{id}', [InventoryController::class, 'destroy']);
Route::get('/api/inventory/logs', [InventoryController::class, 'getLogs']);

use App\Http\Controllers\ProductController;

Route::get('/manager/products', [ProductController::class, 'index']);
Route::post('/api/products', [ProductController::class, 'store']);
Route::post('/api/products/{id}', [ProductController::class, 'update']);
Route::delete('/api/products/{id}', [ProductController::class, 'destroy']);

use App\Http\Controllers\DiscountController;
Route::get('/api/discounts', [DiscountController::class, 'index']);
Route::post('/api/discounts', [DiscountController::class, 'store']);
Route::delete('/api/discounts/{id}', [DiscountController::class, 'destroy']);

use App\Http\Controllers\AddonController;
Route::get('/api/addons', [AddonController::class, 'index']);
Route::post('/api/addons', [AddonController::class, 'store']);
Route::post('/api/addons/{id}', [AddonController::class, 'update']);
Route::delete('/api/addons/{id}', [AddonController::class, 'destroy']);


