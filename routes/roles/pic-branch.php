<?php

use App\Http\Controllers\PicBranch\DashboardController;
use App\Http\Controllers\PicBranch\DeviceController;
use App\Http\Controllers\PicBranch\LicenseController;
use App\Http\Controllers\PicBranch\RenewalController;
use Illuminate\Support\Facades\Route;

// prefix: /pic-branch   name: pic-branch.*   middleware: auth, role:pic_branch
// Semua data otomatis dibatasi ke branch milik user (scope visibleTo + authorizeBranch).

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
Route::get('/licenses/{license}', [LicenseController::class, 'show'])->name('licenses.show');

Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');

Route::get('/renewals', [RenewalController::class, 'index'])->name('renewals.index');
Route::get('/licenses/{license}/renewals/create', [RenewalController::class, 'create'])->name('renewals.create');
Route::post('/licenses/{license}/renewals', [RenewalController::class, 'store'])->name('renewals.store');
Route::get('/renewals/{renewal}', [RenewalController::class, 'show'])->name('renewals.show');
Route::post('/renewals/{renewal}/cancel', [RenewalController::class, 'cancel'])->name('renewals.cancel');
