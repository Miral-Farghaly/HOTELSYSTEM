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

Route::get('/', function () {
    return view('welcome');
});




Route::post('/login',[UserController::class, 'login']);
Route::post('/register',[UserController::class, 'register']);

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:super-admin|manager'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'users'])->name('admin.users');
    Route::get('/rooms', [App\Http\Controllers\Admin\AdminController::class, 'rooms'])->name('admin.rooms');
    Route::get('/reservations', [App\Http\Controllers\Admin\AdminController::class, 'reservations'])->name('admin.reservations');
    Route::get('/reports', [App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('admin.reports');
});

// Staff Routes
Route::prefix('staff')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Staff\StaffController::class, 'dashboard'])->name('staff.dashboard');
    Route::get('/reservations', [App\Http\Controllers\Staff\StaffController::class, 'reservations'])->name('staff.reservations');
});


