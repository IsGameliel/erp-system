<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Organizations</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Customer organizations</h2>
            </div>
            <a href="{{ route('owner.organizations.create') }}" class="btn-primary">New organization</a>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-[1fr_auto]">
        <input class="form-input" name="search" placeholder="Search organization" value="{{ request('search') }}">
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Organization</th>
                    <th class="px-6 py-3 font-medium">Domain</th>
                    <th class="px-6 py-3 font-medium">Subscription</th>
                    <th class="px-6 py-3 font-medium">Users</th>
                    <th class="px-6 py-3 font-medium">Setup</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($organizations as $organization)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $organization->name }}</p>
                            <p class="text-xs text-slate-500">{{ $organization->contact_email ?: 'No contact email' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $organization->primary_domain ?: 'Not connected' }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            <p>{{ $organization->currentSubscriptionPlan?->name ?? 'No plan' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $organization->hasActiveSubscription() ? 'Active until '.optional($organization->subscription_expires_at)->format('M d, Y') : 'Inactive' }}
                            </p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $organization->users_count }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $organization->setupCompleted() ? 'Completed' : 'Pending' }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('owner.organizations.edit', $organization) }}">Edit</a>
                                <form action="{{ route('owner.organizations.destroy', $organization) }}" method="POST" onsubmit="return confirm('Delete this organization?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-6 text-center text-slate-500">No organizations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $organizations->links() }}
</x-app-layout>
