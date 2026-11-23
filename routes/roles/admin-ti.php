<?php

use App\Http\Controllers\AdminTi\ActivityLogController;
use App\Http\Controllers\AdminTi\DashboardController;
use App\Http\Controllers\AdminTi\DeviceController;
use App\Http\Controllers\AdminTi\LicenseController;
use App\Http\Controllers\AdminTi\RenewalController;
use App\Http\Controllers\AdminTi\ReportController;
use Illuminate\Support\Facades\Route;

// prefix: /admin-ti   name: admin-ti.*   middleware: auth, role:admin_ti

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::resource('devices', DeviceController::class);
Route::resource('licenses', LicenseController::class);

Route::get('/renewals', [RenewalController::class, 'index'])->name('renewals.index');
Route::get('/licenses/{license}/renewals/create', [RenewalController::class, 'create'])->name('renewals.create');
Route::post('/licenses/{license}/renewals', [RenewalController::class, 'store'])->name('renewals.store');
Route::get('/renewals/{renewal}', [RenewalController::class, 'show'])->name('renewals.show');
Route::get('/renewals/{renewal}/complete', [RenewalController::class, 'completeForm'])->name('renewals.complete-form');
Route::put('/renewals/{renewal}/complete', [RenewalController::class, 'complete'])->name('renewals.complete');
Route::post('/renewals/{renewal}/cancel', [RenewalController::class, 'cancel'])->name('renewals.cancel');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
