<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Subscription Plans</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Plan management</h2>
            </div>
            <a href="{{ route('owner.subscription-plans.create') }}" class="btn-primary">New plan</a>
        </div>
    </x-slot>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Plan</th>
                    <th class="px-6 py-3 font-medium">Price</th>
                    <th class="px-6 py-3 font-medium">Duration</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($plans as $plan)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $plan->name }}</p>
                            <p class="text-xs text-slate-500">{{ $plan->description ?: 'No description' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">${{ number_format((float) $plan->price, 2) }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $plan->duration_months }} months</td>
                        <td class="px-6 py-4 text-slate-600">{{ $plan->is_active ? 'Active' : 'Hidden' }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('owner.subscription-plans.edit', $plan) }}">Edit</a>
                                <form action="{{ route('owner.subscription-plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Delete this plan?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-6 text-center text-slate-500">No subscription plans yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $plans->links() }}
</x-app-layout>
