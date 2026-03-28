<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Receipt {{ $salesOrder->order_number }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-100 p-6 text-slate-900">
        <div class="mx-auto max-w-3xl rounded-3xl bg-white p-8 shadow-sm print:shadow-none">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-700">Receipt</p>
                    <h1 class="mt-2 text-3xl font-semibold">{{ $salesOrder->order_number }}</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ optional($salesOrder->order_date)->format('M d, Y') }}</p>
                </div>
                <div class="text-right text-sm text-slate-500">
                    <p>{{ config('app.name', 'ERP System') }}</p>
                    <p>{{ $salesOrder->store?->name ?? 'Store not assigned' }}</p>
                    <p>{{ $salesOrder->user?->name }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Customer</p>
                    <p class="mt-2 font-medium">{{ $salesOrder->customer?->full_name }}</p>
                    <p class="text-sm text-slate-500">{{ $salesOrder->customer?->phone }}</p>
                    <p class="text-sm text-slate-500">{{ $salesOrder->customer?->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Order details</p>
                    <p class="mt-2 text-sm">Status: {{ ucfirst($salesOrder->status) }}</p>
                    <p class="text-sm">Payment status: {{ ucfirst($salesOrder->payment_status) }}</p>
                    <p class="text-sm">Payment method: {{ $salesOrder->payment_method ? strtoupper($salesOrder->payment_method) : 'Credit / Pay later' }}</p>
                    @if ($salesOrder->due_date)
                        <p class="text-sm">Due date: {{ optional($salesOrder->due_date)->format('M d, Y') }}</p>
                    @endif
                    <p class="text-sm">Officer: {{ $salesOrder->user?->name }}</p>
                </div>
            </div>

            <table class="mt-8 min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Item</th>
                        <th class="px-4 py-3 font-medium">Qty</th>
                        <th class="px-4 py-3 font-medium">Price</th>
                        <th class="px-4 py-3 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($salesOrder->items as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item->product?->name }}</td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3">₦{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3">₦{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 ml-auto max-w-sm space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>₦{{ number_format($salesOrder->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Tax</span><span>₦{{ number_format($salesOrder->tax, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Discount</span><span>₦{{ number_format($salesOrder->discount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Amount paid</span><span>₦{{ number_format($salesOrder->amount_paid, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Balance impact</span><span>{{ ($salesOrder->amount_paid - $salesOrder->total) > 0 ? '+' : '' }}₦{{ number_format($salesOrder->amount_paid - $salesOrder->total, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Payment</span><span>{{ $salesOrder->payment_method ? strtoupper($salesOrder->payment_method) : 'Credit / Pay later' }}</span></div>
                <div class="flex justify-between border-t border-slate-200 pt-3 text-base font-semibold"><span>Total</span><span>₦{{ number_format($salesOrder->total, 2) }}</span></div>
            </div>

            <div class="mt-8 flex gap-3 print:hidden">
                <button class="rounded-full bg-slate-900 px-5 py-3 text-sm font-medium text-white" onclick="window.print()">Print receipt</button>
                <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700" href="{{ route('sales-orders.show', $salesOrder) }}">Back to order</a>
            </div>
        </div>

        <script>
            window.addEventListener('load', () => window.print());
        </script>
    </body>
</html>
