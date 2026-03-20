<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function sales(Request $request): StreamedResponse|\Illuminate\View\View
    {
        $query = SalesOrder::query()
            ->with(['customer', 'user', 'store'])
            ->when($request->user()?->hasRole(User::ROLE_SALES_OFFICER), fn ($builder) => $builder->where('store_id', $request->user()->store_id))
            ->when($request->filled('from'), fn ($builder) => $builder->whereDate('order_date', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($builder) => $builder->whereDate('order_date', '<=', Carbon::parse($request->string('to'))))
            ->when($request->filled('store_id') && $request->user()?->hasRole(User::ROLE_ADMIN), fn ($builder) => $builder->where('store_id', $request->integer('store_id')));

        $orders = (clone $query)->latest('order_date')->paginate(15)->withQueryString();
        $collection = (clone $query)->get();

        if ($request->string('export') === 'csv') {
            return response()->streamDownload(function () use ($collection) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Order Number', 'Store', 'Customer', 'Date', 'Status', 'Total']);

                foreach ($collection as $order) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->store?->name,
                        $order->customer?->full_name,
                        optional($order->order_date)->format('Y-m-d'),
                        $order->status,
                        $order->total,
                    ]);
                }

                fclose($handle);
            }, 'sales-report.csv');
        }

        return view('reports.sales', [
            'orders' => $orders,
            'totalRevenue' => $collection->sum('total'),
            'deliveredCount' => $collection->where('status', SalesOrder::STATUS_DELIVERED)->count(),
            'orderCount' => $collection->count(),
            'topCustomers' => $collection->groupBy('customer.full_name')->map(fn ($group) => $group->sum('total'))->sortDesc()->take(5),
            'storeSummary' => $collection->groupBy('store.name')->map(fn ($group) => [
                'revenue' => $group->sum('total'),
                'orders' => $group->count(),
            ])->sortByDesc('revenue'),
            'statusSummary' => $collection->groupBy('status')->map->count(),
            'stores' => Store::query()->orderBy('name')->get(),
        ]);
    }

    public function procurement(Request $request): StreamedResponse|\Illuminate\View\View
    {
        $query = PurchaseOrder::query()
            ->with(['vendor', 'user'])
            ->when($request->filled('from'), fn ($builder) => $builder->whereDate('order_date', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($builder) => $builder->whereDate('order_date', '<=', Carbon::parse($request->string('to'))));

        $orders = (clone $query)->latest('order_date')->paginate(15)->withQueryString();
        $collection = (clone $query)->get();

        if ($request->string('export') === 'csv') {
            return response()->streamDownload(function () use ($collection) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['PO Number', 'Vendor', 'Date', 'Status', 'Total']);

                foreach ($collection as $order) {
                    fputcsv($handle, [
                        $order->po_number,
                        $order->vendor?->company_name,
                        optional($order->order_date)->format('Y-m-d'),
                        $order->status,
                        $order->total,
                    ]);
                }

                fclose($handle);
            }, 'procurement-report.csv');
        }

        return view('reports.procurement', [
            'orders' => $orders,
            'totalCost' => $collection->sum('total'),
            'receivedCount' => $collection->where('status', PurchaseOrder::STATUS_RECEIVED)->count(),
            'orderCount' => $collection->count(),
            'topVendors' => $collection->groupBy('vendor.company_name')->map(fn ($group) => $group->sum('total'))->sortDesc()->take(5),
            'statusSummary' => $collection->groupBy('status')->map->count(),
        ]);
    }
}
