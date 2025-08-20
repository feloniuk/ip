<?php

use App\Http\Controllers\Api\IpAddressController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Публічні маршрути автентифікації
Route::post('login', [AuthController::class, 'login']);

// Захищені маршрути
Route::middleware('auth:sanctum')->group(function () {
    // Автентифікація
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });
    
    // API v1
    Route::prefix('v1')->group(function () {
        Route::apiResource('ip-addresses', IpAddressController::class);
    });
});