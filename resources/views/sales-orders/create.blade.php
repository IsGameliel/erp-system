<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Checkout</p>
            <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Create sales order</h2>
        </div>
    </x-slot>

    <form class="space-y-6" method="POST" action="{{ route('sales-orders.store') }}">
        @csrf
        @include('sales-orders._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Checkout order</button>
            <a class="btn-secondary" href="{{ route('sales-orders.index') }}">Cancel</a>
        </div>
    </form>
</x-app-layout>
