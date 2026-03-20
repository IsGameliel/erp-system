<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Reports</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Procurement report</h2>
            </div>
            <a class="btn-secondary" href="{{ route('reports.procurement', array_filter(request()->query() + ['export' => 'csv'])) }}">Export CSV</a>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 md:grid-cols-3">
        <input class="form-input" type="date" name="from" value="{{ request('from') }}">
        <input class="form-input" type="date" name="to" value="{{ request('to') }}">
        <button class="btn-secondary justify-center" type="submit">Apply filters</button>
    </form>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 print:grid-cols-2">
        <x-stat-card title="Total Cost" :value="'$'.number_format($totalCost, 2)" />
        <x-stat-card title="Received Orders" :value="number_format($receivedCount)" />
        <x-stat-card title="Order Count" :value="number_format($orderCount)" />
        <x-stat-card title="Date Range" :value="request('from') || request('to') ? trim((request('from') ?: 'Start').' - '.(request('to') ?: 'Today')) : 'All time'" />
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="page-panel">
            <h3 class="text-lg font-semibold text-slate-950">Top vendors</h3>
            <div class="mt-4 space-y-3">
                @forelse ($topVendors as $name => $value)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                        <span class="text-slate-700">{{ $name ?: 'Unknown vendor' }}</span>
                        <span class="font-semibold text-slate-900">${{ number_format($value, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No procurement data available.</p>
                @endforelse
            </div>
        </div>

        <div class="page-panel xl:col-span-2">
            <h3 class="text-lg font-semibold text-slate-950">Purchase status summary</h3>
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

    <x-table-wrapper class="print:shadow-none">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">PO</th>
                    <th class="px-6 py-3 font-medium">Vendor</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-6 py-4">{{ $order->po_number }}</td>
                        <td class="px-6 py-4">{{ $order->vendor?->company_name }}</td>
                        <td class="px-6 py-4">{{ optional($order->order_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$order->status" /></td>
                        <td class="px-6 py-4">${{ number_format($order->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-6 text-center text-slate-500">No purchase orders found for this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $orders->links() }}
</x-app-layout>
