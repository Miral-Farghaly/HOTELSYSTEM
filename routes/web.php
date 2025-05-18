<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Serve React app for all non-API routes
Route::get('/{path?}', function () {
    return view('app');
})->where('path', '^(?!api).*$');

// API Routes
Route::prefix('api')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);

    // Protected API Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // Admin Routes
        Route::prefix('admin')->middleware(['role:super-admin|manager'])->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard']);
            Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'users']);
            Route::get('/rooms', [App\Http\Controllers\Admin\AdminController::class, 'rooms']);
            Route::get('/reservations', [App\Http\Controllers\Admin\AdminController::class, 'reservations']);
            Route::get('/reports', [App\Http\Controllers\Admin\AdminController::class, 'reports']);
        });

        // Staff Routes
        Route::prefix('staff')->middleware(['role:staff'])->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Staff\StaffController::class, 'dashboard']);
            Route::get('/reservations', [App\Http\Controllers\Staff\StaffController::class, 'reservations']);
        });
    });
});


