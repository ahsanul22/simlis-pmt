<?php

use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\BranchController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\DeviceModelController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\SuperAdmin\SoftwareProductController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\VendorController;
use Illuminate\Support\Facades\Route;

// prefix: /super-admin   name: super-admin.*   middleware: auth, role:super_admin

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::resource('users', UserController::class)->except('show');
Route::resource('branches', BranchController::class)->except('show');
Route::resource('vendors', VendorController::class)->except('show');
Route::resource('device-models', DeviceModelController::class)->except('show');
Route::resource('software-products', SoftwareProductController::class)->except('show');

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
