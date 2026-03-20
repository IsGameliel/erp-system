<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Users</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Create user</h2>
        </div>
    </x-slot>

    <form action="{{ route('users.store') }}" class="page-panel space-y-6" method="POST">
        @csrf

        @include('users._form')

        <div class="flex justify-end gap-3">
            <a class="btn-secondary" href="{{ route('users.index') }}">Cancel</a>
            <button class="btn-primary" type="submit">Create user</button>
        </div>
    </form>
</x-app-layout>
