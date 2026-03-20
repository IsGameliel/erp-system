<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Sales</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Sales order management</h2>
            </div>
            <a href="{{ route('sales-orders.create') }}" class="btn-primary">New sales order</a>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-4">
        <input class="form-input" name="search" placeholder="Search order or customer" value="{{ request('search') }}">
        <select class="form-select" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
            <select class="form-select" name="store_id">
                <option value="">All stores</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" @selected((string) request('store_id') === (string) $store->id)>{{ $store->name }}</option>
                @endforeach
            </select>
        @endif
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Order</th>
                    <th class="px-6 py-3 font-medium">Customer</th>
                    <th class="px-6 py-3 font-medium">Store</th>
                    <th class="px-6 py-3 font-medium">Officer</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Total</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($salesOrders as $salesOrder)
                    <tr>
                        <td class="px-6 py-4"><a class="font-medium text-slate-900 hover:text-cyan-700" href="{{ route('sales-orders.show', $salesOrder) }}">{{ $salesOrder->order_number }}</a></td>
                        <td class="px-6 py-4">{{ $salesOrder->customer?->full_name }}</td>
                        <td class="px-6 py-4">{{ $salesOrder->store?->name ?? 'Unassigned' }}</td>
                        <td class="px-6 py-4">{{ $salesOrder->user?->name }}</td>
                        <td class="px-6 py-4">{{ optional($salesOrder->order_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$salesOrder->status" /></td>
                        <td class="px-6 py-4">${{ number_format($salesOrder->total, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('sales-orders.edit', $salesOrder) }}">Edit</a>
                                <form action="{{ route('sales-orders.destroy', $salesOrder) }}" method="POST" onsubmit="return confirm('Delete this sales order?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-6 text-center text-slate-500">No sales orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $salesOrders->links() }}
</x-app-layout>
