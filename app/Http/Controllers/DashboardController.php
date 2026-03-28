<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $productCategoriesAvailable = ProductCategory::schemaIsReady();

        if ($user?->isSuperAdmin()) {
            return redirect()->route('owner.dashboard');
        }

        return view('dashboard', [
            'totalCustomers' => Customer::count(),
            'totalVendors' => Vendor::count(),
            'totalSalesOrders' => (clone $salesScope = SalesOrder::query()
                ->when($user?->hasRole(User::ROLE_SALES_OFFICER), fn ($query) => $query->where('store_id', $user->store_id)))->count(),
            'totalPurchaseOrders' => PurchaseOrder::count(),
            'totalSalesRevenue' => (clone $salesScope)->sum('total'),
            'totalProcurementCost' => PurchaseOrder::sum('total'),
            'recentSalesOrders' => (clone $salesScope)->with(['customer', 'user', 'store'])->latest()->take(5)->get(),
            'recentPurchaseOrders' => PurchaseOrder::with(['vendor', 'user'])->latest()->take(5)->get(),
            'topCustomers' => Customer::query()
                ->withSum('salesOrders as sales_total', 'total')
                ->withCount('salesOrders')
                ->orderByDesc('sales_total')
                ->limit(5)
                ->get(),
            'topVendors' => Vendor::query()
                ->withSum('purchaseOrders as purchase_total', 'total')
                ->withCount('purchaseOrders')
                ->orderByDesc('purchase_total')
                ->limit(5)
                ->get(),
            'recentActivities' => ActivityLog::schemaIsReady()
                ? ActivityLog::with('user')->latest()->take(8)->get()
                : collect(),
            'salesStatusSummary' => (clone $salesScope)
                ->select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'purchaseStatusSummary' => PurchaseOrder::query()
                ->select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'salesChartLabels' => (clone $salesScope)
                ->selectRaw("DATE_FORMAT(order_date, '%Y-%m') as month")
                ->selectRaw('SUM(total) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->keys()
                ->values(),
            'salesChartValues' => (clone $salesScope)
                ->selectRaw("DATE_FORMAT(order_date, '%Y-%m') as month")
                ->selectRaw('SUM(total) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->values(),
            'procurementChartLabels' => PurchaseOrder::query()
                ->selectRaw("DATE_FORMAT(order_date, '%Y-%m') as month")
                ->selectRaw('SUM(total) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->keys()
                ->values(),
            'procurementChartValues' => PurchaseOrder::query()
                ->selectRaw("DATE_FORMAT(order_date, '%Y-%m') as month")
                ->selectRaw('SUM(total) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->values(),
            'productCategoriesAvailable' => $productCategoriesAvailable,
            'inventoryProducts' => Product::query()
                ->when($productCategoriesAvailable, fn ($query) => $query->with('category'))
                ->orderBy('stock_quantity')
                ->limit(6)
                ->get(),
            'totalProductCategories' => $productCategoriesAvailable ? ProductCategory::count() : 0,
            'recentProductCategories' => $productCategoriesAvailable
                ? ProductCategory::query()->withCount('products')->latest()->limit(4)->get()
                : new Collection(),
            'storeSalesSummary' => Store::query()
                ->withSum('salesOrders as sales_total', 'total')
                ->withCount('salesOrders')
                ->orderByDesc('sales_total')
                ->limit(6)
                ->get(),
            'userStore' => $user?->store,
        ]);
    }

    public function owner()
    {
        return view('owner-dashboard', [
                'totalOrganizations' => Organization::count(),
                'activeOrganizationsCount' => Organization::query()->get()->filter(fn (Organization $organization) => $organization->hasActiveSubscription())->count(),
                'inactiveOrganizationsCount' => Organization::query()->get()->reject(fn (Organization $organization) => $organization->hasActiveSubscription())->count(),
                'pendingPaymentCount' => SubscriptionPayment::query()->where('status', SubscriptionPayment::STATUS_PENDING)->count(),
                'approvedPaymentCount' => SubscriptionPayment::query()->where('status', SubscriptionPayment::STATUS_APPROVED)->count(),
                'subscriptionRevenue' => SubscriptionPayment::query()->where('status', SubscriptionPayment::STATUS_APPROVED)->sum('amount'),
                'subscriptionPlansCount' => SubscriptionPlan::query()->count(),
                'recentSubscriptionPayments' => SubscriptionPayment::query()->with(['organization', 'user', 'plan', 'approver'])->latest()->take(8)->get(),
                'recentOrganizations' => Organization::query()->withCount('users')->latest()->take(8)->get(),
        ]);
    }
}
