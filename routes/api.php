<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiRegisterController;
use App\Http\Controllers\Auth\ApiLoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\GradioController;

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

// Test route to verify API is working
Route::get('test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working!',
        'timestamp' => now()->toISOString(),
    ]);
});

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('register', [ApiRegisterController::class, 'register']);
    Route::post('login', [ApiLoginController::class, 'login']);
    
    // News
    Route::get('news', [NewsController::class, 'index']);
    
    // Image Classification - PUBLIC (no authentication required)
    Route::post('classify-image', [GradioController::class, 'classify']);
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('me', [UserController::class, 'getAuthenticatedUser']);
    Route::put('me', [UserController::class, 'updateAuthenticatedUser']);
    
    // Profile image
    Route::get('me/image', [ProfileImageController::class, 'show']);
    Route::post('me/image', [ProfileImageController::class, 'upload']);
    Route::delete('me/image', [ProfileImageController::class, 'destroy']);

    // Logout
    Route::post('logout', [ApiLoginController::class, 'logout']);
});