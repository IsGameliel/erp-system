<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Edit customer</h2>
    </x-slot>

    <form class="page-panel space-y-6" method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf
        @method('PUT')
        @include('customers._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Update customer</button>
            <a class="btn-secondary" href="{{ route('customers.show', $customer) }}">Back</a>
        </div>
    </form>
</x-app-layout>
