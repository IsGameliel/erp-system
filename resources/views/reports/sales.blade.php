<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Reports</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Sales report</h2>
            </div>
            <a class="btn-secondary" href="{{ route('reports.sales', array_filter(request()->query() + ['export' => 'csv'])) }}">Export CSV</a>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 md:grid-cols-4">
        <input class="form-input" type="date" name="from" value="{{ request('from') }}">
        <input class="form-input" type="date" name="to" value="{{ request('to') }}">
        @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
            <select class="form-select" name="store_id">
                <option value="">All stores</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" @selected((string) request('store_id') === (string) $store->id)>{{ $store->name }}</option>
                @endforeach
            </select>
        @endif
        <button class="btn-secondary justify-center" type="submit">Apply filters</button>
    </form>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 print:grid-cols-2">
        <x-stat-card title="Total Revenue" :value="'$'.number_format($totalRevenue, 2)" />
        <x-stat-card title="Delivered Orders" :value="number_format($deliveredCount)" />
        <x-stat-card title="Order Count" :value="number_format($orderCount)" />
        <x-stat-card title="Date Range" :value="request('from') || request('to') ? trim((request('from') ?: 'Start').' - '.(request('to') ?: 'Today')) : 'All time'" />
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="page-panel">
            <h3 class="text-lg font-semibold text-slate-950">Top customers</h3>
            <div class="mt-4 space-y-3">
                @forelse ($topCustomers as $name => $value)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                        <span class="text-slate-700">{{ $name ?: 'Unknown customer' }}</span>
                        <span class="font-semibold text-slate-900">${{ number_format($value, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No sales data available.</p>
                @endforelse
            </div>
        </div>

        <div class="page-panel xl:col-span-2">
            <h3 class="text-lg font-semibold text-slate-950">Order status summary</h3>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @forelse ($statusSummary as $status => $count)
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                        <div class="flex items-center justify-between">
                            <x-status-badge :status="$status" />
                            <span class="font-semibold text-slate-900">{{ $count }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No status summary available.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="page-panel">
        <h3 class="text-lg font-semibold text-slate-950">Sales by store</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($storeSummary as $storeName => $summary)
                <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm">
                    <p class="font-medium text-slate-900">{{ $storeName ?: 'Unassigned store' }}</p>
                    <p class="mt-2 text-slate-500">{{ $summary['orders'] }} orders</p>
                    <p class="mt-1 text-lg font-semibold text-slate-950">${{ number_format($summary['revenue'], 2) }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No store sales data available.</p>
            @endforelse
        </div>
    </div>

    <x-table-wrapper class="print:shadow-none">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Order</th>
                    <th class="px-6 py-3 font-medium">Store</th>
                    <th class="px-6 py-3 font-medium">Customer</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-6 py-4">{{ $order->order_number }}</td>
                        <td class="px-6 py-4">{{ $order->store?->name ?? 'Unassigned' }}</td>
                        <td class="px-6 py-4">{{ $order->customer?->full_name }}</td>
                        <td class="px-6 py-4">{{ optional($order->order_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$order->status" /></td>
                        <td class="px-6 py-4">${{ number_format($order->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-6 text-center text-slate-500">No sales orders found for this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $orders->links() }}
</x-app-layout>
