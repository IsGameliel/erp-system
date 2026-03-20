<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-semibold tracking-tight text-slate-950">{{ $vendor->company_name }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $vendor->contact_person ?: 'No contact person provided' }}</p>
            </div>
            <a href="{{ route('vendors.edit', $vendor) }}" class="btn-primary">Edit vendor</a>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="page-panel xl:col-span-2">
            <h3 class="text-lg font-semibold text-slate-950">Vendor profile</h3>
            <dl class="mt-4 grid gap-4 md:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Email</dt><dd class="mt-1 text-slate-900">{{ $vendor->email ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Phone</dt><dd class="mt-1 text-slate-900">{{ $vendor->phone ?: 'N/A' }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="mt-1"><x-status-badge :status="$vendor->status" /></dd></div>
                <div><dt class="text-slate-500">Category</dt><dd class="mt-1 text-slate-900 capitalize">{{ $vendor->category ?: 'N/A' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">Address</dt><dd class="mt-1 text-slate-900">{{ $vendor->address ?: 'N/A' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">Notes</dt><dd class="mt-1 text-slate-900">{{ $vendor->notes ?: 'No notes available.' }}</dd></div>
            </dl>
        </div>

        <div class="page-panel">
            <h3 class="text-lg font-semibold text-slate-950">Summary</h3>
            <div class="mt-4 space-y-4 text-sm">
                <div><p class="text-slate-500">Purchase orders</p><p class="mt-1 text-2xl font-semibold text-slate-950">{{ $totalPurchaseOrders }}</p></div>
                <div><p class="text-slate-500">Procurement value</p><p class="mt-1 text-2xl font-semibold text-slate-950">${{ number_format($totalProcurementValue, 2) }}</p></div>
                <div><p class="text-slate-500">Created by</p><p class="mt-1 text-slate-900">{{ $vendor->creator?->name ?: 'System' }}</p></div>
            </div>
        </div>
    </div>

    <x-table-wrapper>
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-950">Purchase order history</h3>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">PO</th>
                    <th class="px-6 py-3 font-medium">Officer</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($vendor->purchaseOrders as $order)
                    <tr>
                        <td class="px-6 py-4"><a class="font-medium text-cyan-700" href="{{ route('purchase-orders.show', $order) }}">{{ $order->po_number }}</a></td>
                        <td class="px-6 py-4">{{ $order->user?->name }}</td>
                        <td class="px-6 py-4">{{ optional($order->order_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$order->status" /></td>
                        <td class="px-6 py-4">${{ number_format($order->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-6 text-center text-slate-500">No purchase orders available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    <div class="page-panel">
        <h3 class="text-lg font-semibold text-slate-950">Activity summary</h3>
        <div class="mt-4 space-y-4">
            @forelse ($vendor->activityLogs as $activity)
                <div class="border-l-2 border-cyan-500 pl-4">
                    <p class="text-sm font-medium text-slate-900">{{ $activity->description }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $activity->created_at->format('M d, Y H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No activity logged for this vendor.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
