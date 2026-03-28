<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Customers</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Customer management</h2>
            </div>
            <a href="{{ route('customers.create') }}" class="btn-primary">New customer</a>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-4">
        <input class="form-input" name="search" placeholder="Search customer" value="{{ request('search') }}">
        <select class="form-select" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select class="form-select" name="customer_type">
            <option value="">All types</option>
            @foreach ($customerTypes as $type)
                <option value="{{ $type }}" @selected(request('customer_type') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Customer</th>
                    <th class="px-6 py-3 font-medium">Contact</th>
                    <th class="px-6 py-3 font-medium">Type</th>
                    <th class="px-6 py-3 font-medium">Discounted Products</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Orders</th>
                    <th class="px-6 py-3 font-medium">Spent</th>
                    <th class="px-6 py-3 font-medium">Acct balance</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($customers as $customer)
                    <tr>
                        <td class="px-6 py-4">
                            <a class="font-medium text-slate-900 hover:text-cyan-700" href="{{ route('customers.show', $customer) }}">{{ $customer->full_name }}</a>
                            <p class="text-xs text-slate-500">{{ $customer->business_name }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <p>{{ $customer->email }}</p>
                            <p>{{ $customer->phone }}</p>
                        </td>
                        <td class="px-6 py-4 capitalize">{{ $customer->customer_type ?: 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $customer->product_discounts_count ? number_format($customer->product_discounts_count).' products' : 'None' }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$customer->status" /></td>
                        <td class="px-6 py-4">{{ $customer->sales_orders_count }}</td>
                        <td class="px-6 py-4">₦{{ number_format($customer->total_amount_spent ?? 0, 2) }}</td>
                        <td class="px-6 py-4 font-medium {{ $customer->account_balance < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                            {{ $customer->account_balance > 0 ? '+' : '' }}₦{{ number_format($customer->account_balance, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('customers.edit', $customer) }}">Edit</a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Delete this customer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-6 py-6 text-center text-slate-500">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $customers->links() }}
</x-app-layout>
