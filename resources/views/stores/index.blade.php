<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Stores</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Store management</h2>
            </div>
            <a href="{{ route('stores.create') }}" class="btn-primary">New store</a>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-[1fr_auto]">
        <input class="form-input" name="search" placeholder="Search store" value="{{ request('search') }}">
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Store</th>
                    <th class="px-6 py-3 font-medium">Location</th>
                    <th class="px-6 py-3 font-medium">Sales officer</th>
                    <th class="px-6 py-3 font-medium">Procurement officer</th>
                    <th class="px-6 py-3 font-medium">Staff</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($stores as $store)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $store->name }}</p>
                            <p class="text-xs text-slate-500">{{ $store->code }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $store->location ?: 'N/A' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $store->salesOfficers->first()?->name ?? 'Unassigned' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $store->procurementOfficers->first()?->name ?? 'Unassigned' }}</td>
                        <td class="px-6 py-4">{{ $store->users_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('stores.edit', $store) }}">Edit</a>
                                <form action="{{ route('stores.destroy', $store) }}" method="POST" onsubmit="return confirm('Delete this store?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-6 text-center text-slate-500">No stores found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $stores->links() }}
</x-app-layout>
