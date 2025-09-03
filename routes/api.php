<?php

use App\Http\Controllers\Api\IpAddressController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    // API v1
    Route::prefix('v1')->group(function () {
        Route::get('ip-addresses/export', [IpAddressController::class, 'export']);

        Route::get('ip-addresses', [IpAddressController::class, 'index']);
        Route::post('ip-addresses', [IpAddressController::class, 'store']);
        Route::get('ip-addresses/{id}', [IpAddressController::class, 'show'])->where('id', '[0-9]+');
        Route::put('ip-addresses/{id}', [IpAddressController::class, 'update'])->where('id', '[0-9]+');
        Route::patch('ip-addresses/{id}', [IpAddressController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('ip-addresses/{id}', [IpAddressController::class, 'destroy'])->where('id', '[0-9]+');
    });
});

Route::get('/test-session', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'session_started' => session()->isStarted(),
        'csrf_token' => csrf_token(),
    ]);
});