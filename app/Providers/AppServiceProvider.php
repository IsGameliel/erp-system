<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\Vendor;
use App\Support\InstallationManager;
use App\Support\TenantDatabaseManager;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        app(InstallationManager::class)->configureFromState();
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $registerTenantBinding = function (string $parameter, string $modelClass): void {
            Route::bind($parameter, function (string $value) use ($modelClass): Model {
                $organization = auth()->user()?->organization ?: app(TenantContext::class)->get();
                app(TenantDatabaseManager::class)->configure($organization);

                return $modelClass::query()->findOrFail($value);
            });
        };

        $registerTenantBinding('customer', Customer::class);
        $registerTenantBinding('salesOrder', SalesOrder::class);
        $registerTenantBinding('sales_order', SalesOrder::class);
        $registerTenantBinding('store', Store::class);
        $registerTenantBinding('vendor', Vendor::class);
        $registerTenantBinding('product', Product::class);
        $registerTenantBinding('purchaseOrder', PurchaseOrder::class);
        $registerTenantBinding('purchase_order', PurchaseOrder::class);

        Route::bind('product_category', function (string $value): ProductCategory {
            $organization = auth()->user()?->organization ?: app(TenantContext::class)->get();
            app(TenantDatabaseManager::class)->configure($organization);

            abort_unless(ProductCategory::schemaIsReady(), 404);

            return ProductCategory::query()->findOrFail($value);
        });

        View::composer('*', function ($view): void {
            $user = auth()->user();
            $tenantOrganization = app(TenantContext::class)->get();

            if (! $tenantOrganization && $user && ! $user->isSuperAdmin()) {
                $tenantOrganization = $user->organization;
            }

            $view->with('applicationBrandName', $tenantOrganization?->displayBrandName() ?: app(InstallationManager::class)->brandName());
            $view->with('tenantOrganization', $tenantOrganization);
        });
    }
}
