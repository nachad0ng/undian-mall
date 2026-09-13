<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BonusPointRuleController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DrawingController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PointExchangeController;
use App\Http\Controllers\Admin\PrizeController;
use App\Http\Controllers\Admin\RafflePeriodController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WinnerController;
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
        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index')
            ->middleware('permission:view-audit-logs');
        Route::get('audit-logs/export', [AuditLogController::class, 'export'])
            ->name('audit-logs.export')
            ->middleware('permission:view-audit-logs');

        // Reports & Exports
        Route::get('reports', [ReportController::class, 'index'])
            ->name('reports.index')
            ->middleware('permission:view-reports|manage-prizes|manage-users');
        Route::get('reports/point-redemptions/export', [ReportController::class, 'exportPointRedemptions'])
            ->name('reports.point-redemptions.export')
            ->middleware('permission:view-reports|manage-prizes|manage-users');
        Route::get('reports/customer-point-balances/export', [ReportController::class, 'exportCustomerPointBalances'])
            ->name('reports.customer-point-balances.export')
            ->middleware('permission:view-reports|manage-prizes|manage-users');
        Route::get('reports/winners/export', [ReportController::class, 'exportWinners'])
            ->name('reports.winners.export')
            ->middleware('permission:view-reports|manage-prizes|manage-users');
        Route::get('reports/audit-logs/export', [ReportController::class, 'exportAuditLogs'])
            ->name('reports.audit-logs.export')
            ->middleware('permission:view-audit-logs');
        // Roles
        Route::resource('roles', RoleController::class)
            ->except(['show'])
            ->middleware('permission:manage-roles');

        // Users
        Route::resource('users', UserController::class)
            ->middleware('permission:manage-users');

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

        // Customers
        Route::get('customers/search', [CustomerController::class, 'search'])
            ->name('customers.search')
            ->middleware('permission:manage-customers');
        Route::post('customers/quick-store', [CustomerController::class, 'store'])
            ->name('customers.quick-store')
            ->middleware('permission:manage-customers');
        Route::resource('customers', CustomerController::class)
            ->middleware('permission:manage-customers');

        // Prizes
        Route::get('prizes/{prize}/draw-preview', [DrawingController::class, 'preview'])
            ->name('prizes.draw-preview')
            ->middleware('permission:manage-draws');
        Route::post('prizes/{prize}/draw', [DrawingController::class, 'draw'])
            ->name('prizes.draw')
            ->middleware('permission:manage-draws');
        Route::post('winners/{winner}/publish', [DrawingController::class, 'publishWinner'])
            ->name('winners.publish')
            ->middleware('permission:manage-draws');
        Route::post('winners/{winner}/unpublish', [DrawingController::class, 'unpublishWinner'])
            ->name('winners.unpublish')
            ->middleware('permission:manage-draws');
        Route::post('prizes/{prize}/move', [PrizeController::class, 'move'])
            ->name('prizes.move')
            ->middleware('permission:manage-prizes');
        Route::resource('prizes', PrizeController::class)
            ->middleware('permission:manage-prizes');

        // Payment Types
        Route::post('payment-types/{payment_type}/toggle-status', [PaymentTypeController::class, 'toggleStatus'])
            ->name('payment-types.toggle-status')
            ->middleware('permission:manage-prizes');
        Route::resource('payment-types', PaymentTypeController::class)
            ->middleware('permission:manage-prizes');

        // Bonus Point Rules
        Route::post('bonus-point-rules/{bonus_point_rule}/toggle-status', [BonusPointRuleController::class, 'toggleStatus'])
            ->name('bonus-point-rules.toggle-status')
            ->middleware('permission:manage-prizes');
        Route::resource('bonus-point-rules', BonusPointRuleController::class)
            ->except(['show'])
            ->middleware('permission:manage-prizes');

        // POINT EXCHANGE (Refactor: rule per-Hadiah, bukan per-Event)
        // Daftar hadiah aktif untuk periode (JSON, untuk consumer pilih hadiah)
        Route::get('periods/{period}/active-prizes', [PointExchangeController::class, 'activePrizesForPeriod'])
            ->name('periods.active-prizes')
            ->middleware('permission:manage-prizes');

        Route::get('customers/{customer}/purchases', [PointExchangeController::class, 'customerPurchases'])
            ->name('customers.purchases')
            ->middleware('permission:manage-prizes');

        // Penukaran poin: customer pilih hadiah + serahkan struk
        Route::post('point-exchange', [PointExchangeController::class, 'store'])
            ->name('point-exchange.store')
            ->middleware('permission:manage-prizes');

        // Saldo poin customer per hadiah
        Route::get('customers/{customer}/point-balances', [PointExchangeController::class, 'customerBalances'])
            ->name('customers.point-balances')
            ->middleware('permission:manage-users');

        // History penukaran poin (admin)
        Route::get('point-exchange', [PointExchangeController::class, 'history'])
            ->name('point-exchange.history')
            ->middleware('permission:manage-prizes');

        Route::get('point-exchange/{redemption}', [PointExchangeController::class, 'show'])
            ->name('point-exchange.show')
            ->middleware('permission:manage-prizes');
    });
});

Route::redirect('/', '/dashboard');
Route::get('/winners', [WinnerController::class, 'index'])->name('winners.index');
