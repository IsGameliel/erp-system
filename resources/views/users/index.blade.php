<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Users</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Admin user management</h2>
            </div>
            <a href="{{ route('users.create') }}" class="btn-primary">New user</a>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-3">
        <input class="form-input" name="search" placeholder="Search user" value="{{ request('search') }}">
        <select class="form-select" name="role">
            <option value="">All roles</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
            @endforeach
        </select>
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Role</th>
                    <th class="px-6 py-3 font-medium">Store</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $managedUser)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $managedUser->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $managedUser->email }}</td>
                        <td class="px-6 py-4 capitalize">{{ str_replace('_', ' ', $managedUser->role) }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $managedUser->store?->name ?? 'Unassigned' }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('users.edit', $managedUser) }}">Edit</a>
                                <form action="{{ route('users.destroy', $managedUser) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-6 text-center text-slate-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $users->links() }}
</x-app-layout>
