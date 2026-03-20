<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Edit product</h2>
    </x-slot>

    <form class="page-panel space-y-6" method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')
        @include('products._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Update product</button>
            <a class="btn-secondary" href="{{ route('products.show', $product) }}">Back</a>
        </div>
    </form>
</x-app-layout>
