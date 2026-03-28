<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-semibold tracking-tight text-slate-950">{{ $purchaseOrder->po_number }}</h2>
                <p class="mt-2 text-sm text-slate-500">Purchase order details and item lines.</p>
            </div>
            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn-primary">Edit order</a>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="page-panel xl:col-span-2">
            <h3 class="text-lg font-semibold text-slate-950">Order details</h3>
            <dl class="mt-4 grid gap-4 md:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Vendor</dt><dd class="mt-1 text-slate-900">{{ $purchaseOrder->vendor?->company_name }}</dd></div>
                <div><dt class="text-slate-500">Procurement officer</dt><dd class="mt-1 text-slate-900">{{ $purchaseOrder->user?->name }}</dd></div>
                <div><dt class="text-slate-500">Order date</dt><dd class="mt-1 text-slate-900">{{ optional($purchaseOrder->order_date)->format('M d, Y') }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="mt-1"><x-status-badge :status="$purchaseOrder->status" /></dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">Notes</dt><dd class="mt-1 text-slate-900">{{ $purchaseOrder->notes ?: 'No notes available.' }}</dd></div>
            </dl>
        </div>
        <div class="page-panel">
            <h3 class="text-lg font-semibold text-slate-950">Totals</h3>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-medium text-slate-900">₦{{ number_format($purchaseOrder->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Tax</span><span class="font-medium text-slate-900">₦{{ number_format($purchaseOrder->tax, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Discount</span><span class="font-medium text-slate-900">₦{{ number_format($purchaseOrder->discount, 2) }}</span></div>
                <div class="flex justify-between border-t border-slate-200 pt-3 text-base"><span class="font-semibold text-slate-700">Total</span><span class="font-semibold text-slate-950">₦{{ number_format($purchaseOrder->total, 2) }}</span></div>
            </div>
        </div>
    </div>

    <x-table-wrapper>
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-950">Item lines</h3>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Product</th>
                    <th class="px-6 py-3 font-medium">Quantity</th>
                    <th class="px-6 py-3 font-medium">Unit cost</th>
                    <th class="px-6 py-3 font-medium">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($purchaseOrder->items as $item)
                    <tr>
                        <td class="px-6 py-4">{{ $item->product?->name }}</td>
                        <td class="px-6 py-4">{{ $item->quantity }}</td>
                        <td class="px-6 py-4">₦{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="px-6 py-4">₦{{ number_format($item->total_cost, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-table-wrapper>

    <div class="page-panel">
        <h3 class="text-lg font-semibold text-slate-950">Activity summary</h3>
        <div class="mt-4 space-y-4">
            @forelse ($purchaseOrder->activityLogs as $activity)
                <div class="border-l-2 border-cyan-500 pl-4">
                    <p class="text-sm font-medium text-slate-900">{{ $activity->description }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $activity->created_at->format('M d, Y H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No activity logged for this order.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
