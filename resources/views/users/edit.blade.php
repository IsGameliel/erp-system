@php($routePrefix = request()->routeIs('owner.*') ? 'owner.' : '')

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Users</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Edit user</h2>
        </div>
    </x-slot>

    <form action="{{ route($routePrefix.'users.update', $managedUser) }}" class="page-panel space-y-6" method="POST">
        @csrf
        @method('PUT')

        @include('users._form')

        <div class="flex justify-end gap-3">
            <a class="btn-secondary" href="{{ route($routePrefix.'users.index') }}">Cancel</a>
            <button class="btn-primary" type="submit">Save changes</button>
        </div>
    </form>

    @if (auth()->user()?->isSuperAdmin() && ! $managedUser->isSuperAdmin())
        <form action="{{ route($routePrefix.'users.subscription.extend', $managedUser) }}" class="page-panel space-y-6" method="POST">
            @csrf

            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Subscription Control</p>
                <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Extend subscriber access</h3>
                <p class="mt-2 text-sm text-slate-500">Use this when you want to manually extend a subscriber beyond the last approved payment.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-[1fr_auto]">
                <div>
                    <label class="text-sm font-medium text-slate-700" for="manual_access_expires_at">New expiry date</label>
                    <input class="form-input" id="manual_access_expires_at" name="access_expires_at" type="date" value="{{ optional($managedUser->access_expires_at)->format('Y-m-d') }}" required>
                </div>

                <div class="self-end">
                    <button class="btn-primary" type="submit">Extend subscription</button>
                </div>
            </div>
        </form>
    @endif
</x-app-layout>
