<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Edit product category</h2>
    </x-slot>

    <form class="page-panel space-y-6" method="POST" action="{{ route('product-categories.update', $category) }}">
        @csrf
        @method('PUT')
        @include('product-categories._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Update category</button>
            <a class="btn-secondary" href="{{ route('product-categories.index') }}">Back</a>
        </div>
    </form>
</x-app-layout>
