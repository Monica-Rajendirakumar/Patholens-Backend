<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiRegisterController;
use App\Http\Controllers\Auth\ApiLoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::post('/register', [ApiRegisterController::class, 'register']);
Route::post('/login', [ApiLoginController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiLoginController::class, 'logout']);
    Route::get('/user', [ApiLoginController::class, 'user']);
    
    // Add more protected routes here
    // Route::get('/profile', [ProfileController::class, 'show']);
    // Route::put('/profile', [ProfileController::class, 'update']);
});