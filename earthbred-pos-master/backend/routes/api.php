<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Laravel Backend!']);
});

// Authentication & Password Management with Brute-Force Rate Limiting
Route::post('/login', [UserController::class, 'login'])->middleware('throttle:5,1');
Route::post('/login/pin', [UserController::class, 'loginWithPin'])->middleware('throttle:10,1');
Route::post('/forgot-password', [UserController::class, 'forgotPassword'])->middleware('throttle:3,1');
Route::post('/reset-password', [UserController::class, 'resetPassword'])->middleware('throttle:5,1');

// Staff Account Management
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::patch('/users/{id}/password', [UserController::class, 'updatePassword']);
Route::patch('/users/{id}/activate', [UserController::class, 'activate']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Void PIN Management with Rate Limiting
Route::post('/void-pin/update', [UserController::class, 'updateVoidPin'])->middleware('throttle:5,1');
Route::post('/void-pin/verify', [UserController::class, 'verifyVoidPin'])->middleware('throttle:5,1');

// Add-ons Management
use App\Http\Controllers\AddonController;
Route::get('/addons', [AddonController::class, 'index']);
Route::post('/addons', [AddonController::class, 'store']);
Route::post('/addons/{id}', [AddonController::class, 'update']);
Route::delete('/addons/{id}', [AddonController::class, 'destroy']);

// Security Audit Logs
use App\Http\Controllers\AuditLogController;
Route::get('/audit-logs', [AuditLogController::class, 'index']);

// Product Management
use App\Http\Controllers\ProductController;
Route::post('/products', [ProductController::class, 'store']);
Route::post('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

