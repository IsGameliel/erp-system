<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Edit sales order</h2>
    </x-slot>

    <form class="space-y-6" method="POST" action="{{ route('sales-orders.update', $salesOrder) }}">
        @csrf
        @method('PUT')
        @include('sales-orders._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Update checkout</button>
            <a class="btn-secondary" href="{{ route('sales-orders.show', $salesOrder) }}">Back</a>
        </div>
    </form>
</x-app-layout>
