<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationOnboardingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SubscriptionPaymentController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! app(\App\Support\InstallationManager::class)->installed()) {
        return redirect()->route('onboarding.create');
    }

    if (auth()->check() && auth()->user()?->isSuperAdmin()) {
        return redirect()->route('owner.dashboard');
    }

    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware(['owner.host', 'guest.install'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::prefix(config('platform.owner_path_prefix'))
    ->middleware(['owner.host', 'installed', 'auth'])
    ->as('owner.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'owner'])->name('dashboard');

        Route::middleware('role:super_admin')->group(function () {
            Route::resource('organizations', OrganizationController::class)->except(['show'])->names('organizations');
            Route::resource('subscription-plans', SubscriptionPlanController::class)->except(['show'])->names('subscription-plans');
            Route::get('/subscriptions', [SubscriptionPaymentController::class, 'index'])->name('subscriptions.index');
            Route::post('/subscriptions/{subscriptionPayment}/approve', [SubscriptionPaymentController::class, 'approve'])->name('subscriptions.approve');
            Route::post('/subscriptions/{subscriptionPayment}/reject', [SubscriptionPaymentController::class, 'reject'])->name('subscriptions.reject');
            Route::post('/subscriptions/{subscriptionPayment}/cancel', [SubscriptionPaymentController::class, 'cancel'])->name('subscriptions.cancel');
            Route::resource('users', UserController::class)->except(['show'])->names('users');
            Route::post('/users/{user}/subscription/extend', [SubscriptionPaymentController::class, 'extend'])->name('users.subscription.extend');
        });
    });

Route::middleware(['installed', 'auth', 'tenant.domain', 'tenant.db', 'active.access'])->group(function () {
    Route::get('/organization/onboarding', [OrganizationOnboardingController::class, 'show'])->name('organizations.onboarding.show');
    Route::post('/organization/onboarding', [OrganizationOnboardingController::class, 'update'])->name('organizations.onboarding.update');

    Route::get('/subscription/status', [SubscriptionPaymentController::class, 'status'])->name('subscriptions.status');
    Route::post('/subscription/pay', [SubscriptionPaymentController::class, 'submit'])->name('subscriptions.pay');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('stores', StoreController::class)->except(['show']);
        Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
    });

    Route::middleware('role:admin,'.User::ROLE_SALES_OFFICER)->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('sales-orders', SalesOrderController::class);
        Route::get('/sales-orders/{salesOrder}/receipt', [SalesOrderController::class, 'receipt'])->name('sales-orders.receipt');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    });

    Route::middleware('role:admin,'.User::ROLE_PROCUREMENT_OFFICER)->group(function () {
        Route::resource('vendors', VendorController::class);
        Route::resource('purchase-orders', PurchaseOrderController::class);
        Route::get('/reports/procurement', [ReportController::class, 'procurement'])->name('reports.procurement');
    });

    Route::middleware('role:admin,'.User::ROLE_SALES_OFFICER.','.User::ROLE_PROCUREMENT_OFFICER)->group(function () {
        Route::resource('products', ProductController::class)->only(['index', 'show']);
    });

    Route::middleware('role:admin,'.User::ROLE_PROCUREMENT_OFFICER)->group(function () {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });
});

require __DIR__.'/auth.php';
