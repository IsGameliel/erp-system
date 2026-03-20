<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold tracking-tight text-slate-950">Edit vendor</h2>
    </x-slot>

    <form class="page-panel space-y-6" method="POST" action="{{ route('vendors.update', $vendor) }}">
        @csrf
        @method('PUT')
        @include('vendors._form')
        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Update vendor</button>
            <a class="btn-secondary" href="{{ route('vendors.show', $vendor) }}">Back</a>
        </div>
    </form>
</x-app-layout>
