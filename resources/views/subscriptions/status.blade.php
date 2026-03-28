<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Subscription</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Plan selection and status</h2>
            </div>
            <p class="text-sm text-slate-500">Choose a plan, submit payment details, then wait for owner approval.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <div class="page-panel space-y-6">
            <div>
                <h3 class="text-xl font-semibold text-slate-950">Current access</h3>
                <p class="mt-2 text-sm text-slate-500">After onboarding, your organization admin can select a plan here and submit payment information for owner approval.</p>
            </div>

            @if ($organization)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    <p class="font-medium text-slate-900">{{ $organization->name }}</p>
                    <p class="mt-1">Brand: {{ $organization->displayBrandName() }}</p>
                    <p class="mt-1">Setup: {{ $organization->setupCompleted() ? 'Completed' : 'Pending' }}</p>
                    <p class="mt-1">Subscription: {{ $organization->hasActiveSubscription() ? 'Active until '.optional($organization->subscription_expires_at)->format('M d, Y') : 'Inactive' }}</p>
                    <p class="mt-1">Current plan: {{ $organization->currentSubscriptionPlan?->name ?? 'No active plan' }}</p>
                </div>
            @else
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                    No organization is assigned to your account yet. Contact the owner.
                </div>
            @endif

            @if ($organization)
                <form action="{{ route('subscriptions.pay') }}" class="space-y-6" method="POST">
                    @csrf

                    <div class="grid gap-4">
                        @forelse ($plans as $plan)
                            <label class="flex cursor-pointer items-start gap-4 rounded-3xl border border-slate-200 bg-white px-5 py-4 transition hover:border-cyan-300">
                                <input class="mt-1 rounded border-slate-300 text-cyan-700 focus:ring-cyan-500" name="subscription_plan_id" type="radio" value="{{ $plan->id }}" @checked(old('subscription_plan_id') == $plan->id) required>
                                <span class="flex-1">
                                    <span class="flex items-center justify-between gap-4">
                                        <span class="text-lg font-semibold text-slate-950">{{ $plan->name }}</span>
                                        <span class="text-lg font-semibold text-cyan-700">${{ number_format((float) $plan->price, 2) }}</span>
                                    </span>
                                    <span class="mt-2 block text-sm text-slate-500">{{ $plan->duration_months }} months access</span>
                                    @if ($plan->description)
                                        <span class="mt-2 block text-sm text-slate-600">{{ $plan->description }}</span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">No active plans are available yet. Contact the owner.</p>
                        @endforelse
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="payment_reference">Payment reference</label>
                        <input class="form-input mt-2" id="payment_reference" name="payment_reference" type="text" value="{{ old('payment_reference') }}" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="notes">Notes</label>
                        <textarea class="form-input mt-2 min-h-28" id="notes" name="notes">{{ old('notes') }}</textarea>
                    </div>

                    <button class="btn-primary" type="submit">Submit plan and payment</button>
                </form>
            @endif
        </div>

        <div class="page-panel">
            <h3 class="text-xl font-semibold text-slate-950">Subscription history</h3>
            <div class="mt-4 space-y-3">
                @forelse ($payments as $payment)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <p class="font-medium text-slate-900">{{ $payment->plan?->name ?? 'Custom plan' }}</p>
                            <p class="text-sm uppercase tracking-wide text-slate-500">{{ $payment->status }}</p>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">Managed by owner</p>
                        <p class="mt-1 text-sm text-slate-600">Amount: ${{ number_format((float) $payment->amount, 2) }}</p>
                        @if ($payment->ends_at)
                            <p class="mt-1 text-sm text-slate-600">Access until {{ $payment->ends_at->format('M d, Y') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No subscription records yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
