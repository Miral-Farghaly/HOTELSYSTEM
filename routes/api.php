<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Admin\MonitoringController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Admin\StatisticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->name('api.')->group(function () {
    // Auth routes
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
    
    // Room routes (public)
    Route::get('rooms', [RoomController::class, 'index']);
    Route::get('rooms/available', [RoomController::class, 'available']);
    Route::get('rooms/{room}', [RoomController::class, 'show']);
    Route::get('rooms/{room}/availability', [RoomController::class, 'checkAvailability']);
    
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
    
    // Room management routes
    Route::middleware('permission:manage-rooms')->group(function () {
        Route::post('rooms', [RoomController::class, 'store']);
        Route::put('rooms/{room}', [RoomController::class, 'update']);
        Route::delete('rooms/{room}', [RoomController::class, 'destroy']);
        Route::post('rooms/{room}/maintenance', [RoomController::class, 'maintenance']);
    });

    // Booking routes
    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{booking}', [BookingController::class, 'show']);
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

        // Reports
        Route::get('reports/occupancy', [ReportController::class, 'occupancy']);
        Route::get('reports/revenue', [ReportController::class, 'revenue']);
        Route::get('reports/maintenance', [ReportController::class, 'maintenance']);
    });

    // Room reservations
    Route::get('rooms/{room}/reservations', [RoomController::class, 'reservations'])
        ->name('rooms.reservations');
    Route::get('rooms/{room}/maintenance-logs', [RoomController::class, 'maintenanceLogs'])
        ->name('rooms.maintenance-logs');

    // Reservations
    Route::apiResource('reservations', ReservationController::class);
    Route::post('reservations/{reservation}/check-in', [ReservationController::class, 'checkIn']);
    Route::post('reservations/{reservation}/check-out', [ReservationController::class, 'checkOut']);
    Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);

    // User profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::put('profile/password', [ProfileController::class, 'updatePassword']);

    // Maintenance Categories
    Route::apiResource('maintenance/categories', 'App\Http\Controllers\Api\V1\MaintenanceCategoryController');

    // Maintenance Tasks
    Route::apiResource('maintenance/tasks', 'App\Http\Controllers\Api\V1\MaintenanceTaskController');
    Route::post('maintenance/tasks/{task}/complete', 'App\Http\Controllers\Api\V1\MaintenanceTaskController@complete');

    // Maintenance Inventory
    Route::apiResource('maintenance/inventory', 'App\Http\Controllers\Api\V1\MaintenanceInventoryController');
    Route::post('maintenance/inventory/{item}/adjust', 'App\Http\Controllers\Api\V1\MaintenanceInventoryController@adjust');

    // Staff Skills
    Route::apiResource('staff/skills', 'App\Http\Controllers\Api\V1\StaffSkillController');
    Route::post('staff/skills/{skill}/verify', 'App\Http\Controllers\Api\V1\StaffSkillController@verify');

    // Payment routes
    Route::post('/payments', [PaymentController::class, 'processPayment']);
    Route::get('/payments/{transactionId}', [PaymentController::class, 'getStatus']);
    Route::post('/payments/{transactionId}/refund', [PaymentController::class, 'refund']);
});

// Admin routes
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // User management
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/{user}/roles', [UserController::class, 'assignRole']);

    // Room management
    Route::get('/rooms', [AdminRoomController::class, 'index']);
    Route::post('/rooms', [AdminRoomController::class, 'store']);
    Route::put('/rooms/{room}', [AdminRoomController::class, 'update']);
    Route::post('/rooms/{room}/maintenance', [AdminRoomController::class, 'toggleMaintenance']);

    // Booking management
    Route::get('/bookings', [AdminBookingController::class, 'index']);

    // Statistics
    Route::get('/statistics/bookings', [StatisticsController::class, 'bookings']);
}); 