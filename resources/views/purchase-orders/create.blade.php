<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Create purchase order</h2>
    </x-slot>

    <form class="space-y-6" method="POST" action="{{ route('purchase-orders.store') }}">
        @csrf
        @include('purchase-orders._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Save purchase order</button>
            <a class="btn-secondary" href="{{ route('purchase-orders.index') }}">Cancel</a>
        </div>
    </form>
</x-app-layout>
