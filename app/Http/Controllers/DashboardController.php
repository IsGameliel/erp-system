<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $salesScope = SalesOrder::query()
            ->when($user?->hasRole(User::ROLE_SALES_OFFICER), fn ($query) => $query->where('store_id', $user->store_id));

        $salesMonthly = (clone $salesScope)
            ->selectRaw("DATE_FORMAT(order_date, '%Y-%m') as month")
            ->selectRaw('SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $procurementMonthly = PurchaseOrder::query()
            ->selectRaw("DATE_FORMAT(order_date, '%Y-%m') as month")
            ->selectRaw('SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $topCustomers = Customer::query()
            ->withSum('salesOrders as sales_total', 'total')
            ->withCount('salesOrders')
            ->orderByDesc('sales_total')
            ->limit(5)
            ->get();

        $topVendors = Vendor::query()
            ->withSum('purchaseOrders as purchase_total', 'total')
            ->withCount('purchaseOrders')
            ->orderByDesc('purchase_total')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'totalCustomers' => Customer::count(),
            'totalVendors' => Vendor::count(),
            'totalSalesOrders' => (clone $salesScope)->count(),
            'totalPurchaseOrders' => PurchaseOrder::count(),
            'totalSalesRevenue' => (clone $salesScope)->sum('total'),
            'totalProcurementCost' => PurchaseOrder::sum('total'),
            'recentSalesOrders' => (clone $salesScope)->with(['customer', 'user', 'store'])->latest()->take(5)->get(),
            'recentPurchaseOrders' => PurchaseOrder::with(['vendor', 'user'])->latest()->take(5)->get(),
            'topCustomers' => $topCustomers,
            'topVendors' => $topVendors,
            'recentActivities' => ActivityLog::with('user')->latest()->take(8)->get(),
            'salesStatusSummary' => (clone $salesScope)
                ->select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'purchaseStatusSummary' => PurchaseOrder::query()
                ->select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'salesChartLabels' => $salesMonthly->keys()->values(),
            'salesChartValues' => $salesMonthly->values(),
            'procurementChartLabels' => $procurementMonthly->keys()->values(),
            'procurementChartValues' => $procurementMonthly->values(),
            'inventoryProducts' => Product::query()->orderBy('stock_quantity')->limit(6)->get(),
            'storeSalesSummary' => Store::query()
                ->withSum('salesOrders as sales_total', 'total')
                ->withCount('salesOrders')
                ->orderByDesc('sales_total')
                ->limit(6)
                ->get(),
            'userStore' => $user?->store,
        ]);
    }
}
