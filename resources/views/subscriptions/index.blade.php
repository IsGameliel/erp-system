<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Subscriptions</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Payment records</h2>
            </div>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-[1fr_auto]">
        <select class="form-select" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Organization</th>
                    <th class="px-6 py-3 font-medium">Plan</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Amount</th>
                    <th class="px-6 py-3 font-medium">Access</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $payment->organization?->name ?? 'Deleted organization' }}</p>
                            <p class="text-xs text-slate-500">{{ $payment->user?->name ?? 'Unknown submitter' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $payment->plan?->name ?? 'Custom plan' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ ucfirst($payment->status) }}</td>
                        <td class="px-6 py-4 text-slate-600">${{ number_format((float) $payment->amount, 2) }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            @if ($payment->starts_at && $payment->ends_at)
                                {{ $payment->starts_at->format('M d, Y') }} to {{ $payment->ends_at->format('M d, Y') }}
                            @else
                                Not activated
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                @if ($payment->status === \App\Models\SubscriptionPayment::STATUS_PENDING)
                                    <form action="{{ route('owner.subscriptions.approve', $payment) }}" method="POST">
                                        @csrf
                                        <button class="text-cyan-700 hover:text-cyan-900" type="submit">Approve</button>
                                    </form>
                                    <form action="{{ route('owner.subscriptions.reject', $payment) }}" method="POST">
                                        @csrf
                                        <button class="text-amber-700 hover:text-amber-900" type="submit">Reject</button>
                                    </form>
                                @endif

                                @if ($payment->status === \App\Models\SubscriptionPayment::STATUS_APPROVED)
                                    <form action="{{ route('owner.subscriptions.cancel', $payment) }}" method="POST" onsubmit="return confirm('Cancel this subscription?');">
                                        @csrf
                                        <button class="text-rose-600 hover:text-rose-800" type="submit">Cancel</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-6 text-center text-slate-500">No payment records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $payments->links() }}
</x-app-layout>
