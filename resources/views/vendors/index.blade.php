<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Vendors</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Vendor management</h2>
            </div>
            <a href="{{ route('vendors.create') }}" class="btn-primary">New vendor</a>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-4">
        <input class="form-input" name="search" placeholder="Search vendor" value="{{ request('search') }}">
        <select class="form-select" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select class="form-select" name="category">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ ucfirst($category) }}</option>
            @endforeach
        </select>
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Vendor</th>
                    <th class="px-6 py-3 font-medium">Contact</th>
                    <th class="px-6 py-3 font-medium">Category</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Orders</th>
                    <th class="px-6 py-3 font-medium">Value</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($vendors as $vendor)
                    <tr>
                        <td class="px-6 py-4">
                            <a class="font-medium text-slate-900 hover:text-cyan-700" href="{{ route('vendors.show', $vendor) }}">{{ $vendor->company_name }}</a>
                            <p class="text-xs text-slate-500">{{ $vendor->contact_person }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <p>{{ $vendor->email }}</p>
                            <p>{{ $vendor->phone }}</p>
                        </td>
                        <td class="px-6 py-4 capitalize">{{ $vendor->category ?: 'N/A' }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$vendor->status" /></td>
                        <td class="px-6 py-4">{{ $vendor->purchase_orders_count }}</td>
                        <td class="px-6 py-4">${{ number_format($vendor->total_procurement_value ?? 0, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('vendors.edit', $vendor) }}">Edit</a>
                                <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" onsubmit="return confirm('Delete this vendor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-6 text-center text-slate-500">No vendors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $vendors->links() }}
</x-app-layout>
