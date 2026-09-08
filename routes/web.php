<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RafflePeriodController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\PrizeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Roles
        Route::resource('roles', RoleController::class)
            ->except(['show'])
            ->middleware('permission:manage-roles');

        // Permissions
        Route::resource('permissions', PermissionController::class)
            ->except(['show'])
            ->middleware('permission:manage-permissions');

        // Raffle Periods
        Route::post('raffle-periods/{raffle_period}/toggle-status', [RafflePeriodController::class, 'toggleStatus'])
            ->name('raffle-periods.toggle-status')
            ->middleware('permission:manage-periods');
        Route::resource('raffle-periods', RafflePeriodController::class)
            ->middleware('permission:manage-periods');

        // Tenants
        Route::post('tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])
            ->name('tenants.toggle-status')
            ->middleware('permission:manage-tenants');
        Route::resource('tenants', TenantController::class)
            ->middleware('permission:manage-tenants');

        // Prizes
        Route::post('prizes/{prize}/move', [PrizeController::class, 'move'])
            ->name('prizes.move')
            ->middleware('permission:manage-prizes');
        Route::resource('prizes', PrizeController::class)
            ->middleware('permission:manage-prizes');
    });
});

Route::redirect('/', '/dashboard');
