<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-semibold tracking-tight text-slate-950">{{ $customer->full_name }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $customer->business_name ?: 'No business name provided' }}</p>
            </div>
            <a href="{{ route('customers.edit', $customer) }}" class="btn-primary">Edit customer</a>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="page-panel xl:col-span-2">
            <h3 class="text-lg font-semibold text-slate-950">Profile information</h3>
            <dl class="mt-4 grid gap-4 md:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Email</dt><dd class="mt-1 text-slate-900">{{ $customer->email ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Phone</dt><dd class="mt-1 text-slate-900">{{ $customer->phone ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="mt-1"><x-status-badge :status="$customer->status" /></dd></div>
                <div><dt class="text-slate-500">Customer type</dt><dd class="mt-1 text-slate-900 capitalize">{{ $customer->customer_type ?: 'N/A' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">Address</dt><dd class="mt-1 text-slate-900">{{ $customer->address ?: 'N/A' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">Notes</dt><dd class="mt-1 text-slate-900">{{ $customer->notes ?: 'No notes available.' }}</dd></div>
            </dl>
        </div>

        <div class="page-panel">
            <h3 class="text-lg font-semibold text-slate-950">Summary</h3>
            <div class="mt-4 space-y-4 text-sm">
                <div><p class="text-slate-500">Total orders</p><p class="mt-1 text-2xl font-semibold text-slate-950">{{ $totalOrders }}</p></div>
                <div><p class="text-slate-500">Total amount spent</p><p class="mt-1 text-2xl font-semibold text-slate-950">${{ number_format($totalAmountSpent, 2) }}</p></div>
                <div><p class="text-slate-500">Created by</p><p class="mt-1 text-slate-900">{{ $customer->creator?->name ?: 'System' }}</p></div>
            </div>
        </div>
    </div>

    <x-table-wrapper>
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-950">Sales order history</h3>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Order</th>
                    <th class="px-6 py-3 font-medium">Officer</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($customer->salesOrders as $order)
                    <tr>
                        <td class="px-6 py-4"><a class="font-medium text-cyan-700" href="{{ route('sales-orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td class="px-6 py-4">{{ $order->user?->name }}</td>
                        <td class="px-6 py-4">{{ optional($order->order_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$order->status" /></td>
                        <td class="px-6 py-4">${{ number_format($order->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-6 text-center text-slate-500">No sales orders available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    <div class="page-panel">
        <h3 class="text-lg font-semibold text-slate-950">Activity summary</h3>
        <div class="mt-4 space-y-4">
            @forelse ($customer->activityLogs as $activity)
                <div class="border-l-2 border-cyan-500 pl-4">
                    <p class="text-sm font-medium text-slate-900">{{ $activity->description }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $activity->created_at->format('M d, Y H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No activity logged for this customer.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
