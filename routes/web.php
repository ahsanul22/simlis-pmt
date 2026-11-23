<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Route umum (auth, profil, notifikasi) ada di file ini.
| Route tiap role ada di routes/roles/{role}.php, dengan konvensi:
|   URL    : /{role-prefix}/...           mis. /admin-ti/licenses
|   Nama   : {role-prefix}.{resource}.*   mis. admin-ti.licenses.index
|   View   : resources/views/{role-prefix}/...
|   Ctrl   : App\Http\Controllers\{RoleStudly}\...
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Arahkan ke dashboard sesuai role
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->role->dashboardRoute()))->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');

    Route::prefix('super-admin')->name('super-admin.')->middleware('role:super_admin')
        ->group(base_path('routes/roles/super-admin.php'));

    Route::prefix('admin-ti')->name('admin-ti.')->middleware('role:admin_ti')
        ->group(base_path('routes/roles/admin-ti.php'));

    Route::prefix('manajemen')->name('manajemen.')->middleware('role:manajemen')
        ->group(base_path('routes/roles/manajemen.php'));

    Route::prefix('pic-branch')->name('pic-branch.')->middleware('role:pic_branch')
        ->group(base_path('routes/roles/pic-branch.php'));
});
