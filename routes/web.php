<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('stores', StoreController::class)->except(['show']);
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
        Route::resource('products', ProductController::class);
    });
});

require __DIR__.'/auth.php';
