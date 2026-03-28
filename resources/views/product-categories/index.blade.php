<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Catalog</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Product categories</h2>
            </div>
            <a href="{{ route('product-categories.create') }}" class="btn-primary">New category</a>
        </div>
    </x-slot>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Category</th>
                    <th class="px-6 py-3 font-medium">Description</th>
                    <th class="px-6 py-3 font-medium">Products</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $category->description ?: 'No description provided.' }}</td>
                        <td class="px-6 py-4">{{ $category->products_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('product-categories.edit', $category) }}">Edit</a>
                                <form action="{{ route('product-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category? Products in it will become uncategorized.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-6 text-center text-slate-500">No product categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $categories->links() }}
</x-app-layout>
