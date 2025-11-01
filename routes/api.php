<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Telegram webhook (no auth)
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Shipping calculation (accessible from web forms)
Route::post('/calculate-shipping', [OrderController::class, 'calculateShipping']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    // Complaints
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show']);
    Route::put('/complaints/{complaint}', [ComplaintController::class, 'update']);
    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy']);

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{address}', [AddressController::class, 'show']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{address}/set-primary', [AddressController::class, 'setPrimary']);

    // Location data for RajaOngkir
    Route::get('/provinces', [AddressController::class, 'getProvinces']);
    Route::get('/cities', [AddressController::class, 'getCitiesApi']);
    Route::get('/districts', [AddressController::class, 'getDistrictsApi']);

    // Order tracking and labels
    Route::post('/orders/{order}/track', [OrderController::class, 'trackOrder']);
    Route::post('/orders/{order}/generate-label', [OrderController::class, 'generateLabel']);

    // Order status updates
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);


});
