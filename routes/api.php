<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {
    // Authentication & Password Management with Brute-Force Rate Limiting
    Route::post('/login', [UserController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/login/pin', [UserController::class, 'loginWithPin'])->middleware('throttle:10,1');
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/forgot-password', [UserController::class, 'forgotPassword'])->middleware('throttle:10,1');
    Route::post('/reset-password', [UserController::class, 'resetPassword'])->middleware('throttle:5,1');

    // POS Void PIN Verification (Terminal override check)
    Route::post('/void-pin/verify', [UserController::class, 'verifyVoidPin'])->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Cashier & Management Shared API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'role:cashier,manager,owner'])->group(function () {
    Route::get('/addons', [AddonController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Manager & Owner Protected API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'role:manager,owner'])->group(function () {
    Route::post('/addons', [AddonController::class, 'store']);
    Route::post('/addons/{id}', [AddonController::class, 'update']);
    Route::delete('/addons/{id}', [AddonController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Owner Exclusive Administration API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'role:owner'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{id}/password', [UserController::class, 'updatePassword']);
    Route::patch('/users/{id}/activate', [UserController::class, 'activate']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::post('/void-pin/update', [UserController::class, 'updateVoidPin'])->middleware('throttle:5,1');
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
});
