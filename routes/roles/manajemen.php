<?php

use App\Http\Controllers\Manajemen\ActivityLogController;
use App\Http\Controllers\Manajemen\ApprovalController;
use App\Http\Controllers\Manajemen\DashboardController;
use App\Http\Controllers\Manajemen\LicenseController;
use App\Http\Controllers\Manajemen\ReportController;
use Illuminate\Support\Facades\Route;

// prefix: /manajemen   name: manajemen.*   middleware: auth, role:manajemen

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
Route::get('/approvals/{renewal}', [ApprovalController::class, 'show'])->name('approvals.show');
Route::post('/approvals/{renewal}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
Route::post('/approvals/{renewal}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');

Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
Route::get('/licenses/{license}', [LicenseController::class, 'show'])->name('licenses.show');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
