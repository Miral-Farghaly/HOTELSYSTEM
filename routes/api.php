<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Admin\MonitoringController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
    
    // Room routes (public)
    Route::get('rooms/available', [RoomController::class, 'available']);
    
    // Payment webhook
    Route::post('payments/webhook', [PaymentController::class, 'handleWebhook']);

    // Health check (public)
    Route::get('health', [MonitoringController::class, 'health']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
    
    // Booking routes
    Route::apiResource('bookings', BookingController::class);
    Route::put('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    
    // Payment routes
    Route::post('payments/create-intent', [PaymentController::class, 'createIntent']);
    Route::post('payments/confirm', [PaymentController::class, 'confirm']);
    Route::post('payments/{payment}/refund', [PaymentController::class, 'refund']);
    
    // Admin routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('users', [AdminController::class, 'users']);
        Route::post('users/{user}/roles', [AdminController::class, 'manageUserRoles']);
        
        Route::apiResource('rooms', RoomController::class)->except(['index', 'show']);
        Route::post('rooms/{room}/maintenance', [RoomController::class, 'maintenance']);
        
        Route::get('bookings', [AdminController::class, 'bookings']);
        Route::get('statistics/bookings', [AdminController::class, 'bookingStatistics']);

        // Monitoring routes
        Route::prefix('monitoring')->group(function () {
            Route::get('metrics', [MonitoringController::class, 'metrics']);
            Route::get('logs', [MonitoringController::class, 'logs']);
            Route::get('errors', [MonitoringController::class, 'errorStats']);
        });
    });
}); 