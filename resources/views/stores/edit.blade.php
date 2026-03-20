<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Stores</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Edit store</h2>
        </div>
    </x-slot>

    <form action="{{ route('stores.update', $store) }}" class="page-panel space-y-6" method="POST">
        @csrf
        @method('PUT')

        @include('stores._form')

        <div class="flex justify-end gap-3">
            <a class="btn-secondary" href="{{ route('stores.index') }}">Cancel</a>
            <button class="btn-primary" type="submit">Save changes</button>
        </div>
    </form>
</x-app-layout>
