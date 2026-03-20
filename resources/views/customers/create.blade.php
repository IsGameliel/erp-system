<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Create customer</h2>
    </x-slot>

    <form class="page-panel space-y-6" method="POST" action="{{ route('customers.store') }}">
        @csrf
        @include('customers._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Save customer</button>
            <a class="btn-secondary" href="{{ route('customers.index') }}">Cancel</a>
        </div>
    </form>
</x-app-layout>
