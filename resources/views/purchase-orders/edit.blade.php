<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Edit purchase order</h2>
    </x-slot>

    <form class="space-y-6" method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}">
        @csrf
        @method('PUT')
        @include('purchase-orders._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Update purchase order</button>
            <a class="btn-secondary" href="{{ route('purchase-orders.show', $purchaseOrder) }}">Back</a>
        </div>
    </form>
</x-app-layout>
