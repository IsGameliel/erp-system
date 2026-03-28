<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Owner Dashboard</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Platform subscription overview</h2>
            </div>
            <p class="text-sm text-slate-500">Monitor organizations, subscriptions, payment approvals, and platform revenue.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-stat-card title="Organizations" :value="number_format($totalOrganizations)" />
        <x-stat-card title="Active Organizations" :value="number_format($activeOrganizationsCount)" />
        <x-stat-card title="Inactive Organizations" :value="number_format($inactiveOrganizationsCount)" />
        <x-stat-card title="Pending Payments" :value="number_format($pendingPaymentCount)" />
        <x-stat-card title="Approved Payments" :value="number_format($approvedPaymentCount)" />
        <x-stat-card title="Revenue" :value="'$'.number_format($subscriptionRevenue, 2)" />
        <x-stat-card title="Subscription Plans" :value="number_format($subscriptionPlansCount)" />
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Recent organizations</h3>
                    <p class="mt-1 text-sm text-slate-500">See which customer organizations have finished onboarding and how many users each one has.</p>
                </div>
                <a href="{{ route('owner.organizations.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-900">Manage organizations</a>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($recentOrganizations as $organization)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4">
                        <div>
                            <p class="font-medium text-slate-900">{{ $organization->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $organization->users_count }} users</p>
                        </div>
                        <p class="text-sm text-slate-600">{{ $organization->setupCompleted() ? 'Onboarded' : 'Setup pending' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No organizations created yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Owner controls</h3>
                    <p class="mt-1 text-sm text-slate-500">Control customers, plans, and payment approvals from one place.</p>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                <a href="{{ route('owner.organizations.index') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                    <span>Organizations</span>
                    <span class="font-semibold text-cyan-700">Open</span>
                </a>
                <a href="{{ route('owner.users.index') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                    <span>Users and organization admins</span>
                    <span class="font-semibold text-cyan-700">Open</span>
                </a>
                <a href="{{ route('owner.subscription-plans.index') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                    <span>Subscription plans</span>
                    <span class="font-semibold text-cyan-700">Open</span>
                </a>
                <a href="{{ route('owner.subscriptions.index') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                    <span>Payments</span>
                    <span class="font-semibold text-cyan-700">Open</span>
                </a>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-950">Recent payment submissions</h3>
                <p class="mt-1 text-sm text-slate-500">Review the latest organization subscription requests and their status.</p>
            </div>
            <a href="{{ route('owner.subscriptions.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-900">View all payments</a>
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($recentSubscriptionPayments as $payment)
                <div class="flex items-start justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ $payment->organization?->name ?? 'Deleted organization' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $payment->plan?->name ?? 'Custom plan' }} • {{ ucfirst($payment->status) }}</p>
                        <p class="mt-1 text-xs text-slate-500">Submitted by {{ $payment->user?->name ?? 'Unknown user' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-slate-900">${{ number_format((float) $payment->amount, 2) }}</p>
                        @if ($payment->ends_at)
                            <p class="mt-1 text-xs text-slate-500">Until {{ $payment->ends_at->format('M d, Y') }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No subscription payments yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
