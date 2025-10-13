<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiRegisterController;
use App\Http\Controllers\Auth\ApiLoginController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 🟢 Public routes
Route::post('/register', [ApiRegisterController::class, 'register']);
Route::post('/login', [ApiLoginController::class, 'login']);

// 🔒 Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiLoginController::class, 'logout']);
    Route::get('/me', [UserController::class, 'getAuthenticatedUser']);
});
