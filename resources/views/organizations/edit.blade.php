<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Organizations</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Edit organization</h2>
        </div>
    </x-slot>

    <form action="{{ route('owner.organizations.update', $organization) }}" class="page-panel space-y-6" method="POST">
        @csrf
        @method('PUT')

        @include('organizations._form')

        <div class="flex justify-end gap-3">
            <a class="btn-secondary" href="{{ route('owner.organizations.index') }}">Cancel</a>
            <button class="btn-primary" type="submit">Save changes</button>
        </div>
    </form>
</x-app-layout>
