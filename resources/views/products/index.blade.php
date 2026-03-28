<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Products</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Product catalog</h2>
            </div>
            @if (! auth()->user()->hasRole(\App\Models\User::ROLE_SALES_OFFICER))
                <a href="{{ route('products.create') }}" class="btn-primary">New product</a>
            @endif
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-4">
        <input class="form-input" name="search" placeholder="Search product or SKU" value="{{ request('search') }}">
        <select class="form-select" name="category_id" @disabled(! $productCategoriesAvailable)>
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select class="form-select" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    @if (! $productCategoriesAvailable)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Product categories are unavailable for this organization until the latest tenant database migration is applied.
        </div>
    @endif

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Product</th>
                    <th class="px-6 py-3 font-medium">Category</th>
                    <th class="px-6 py-3 font-medium">SKU</th>
                    <th class="px-6 py-3 font-medium">Prices</th>
                    <th class="px-6 py-3 font-medium">Stock</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <a class="font-medium text-slate-900 hover:text-cyan-700" href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $product->category?->name ?? 'Uncategorized' }}</td>
                        <td class="px-6 py-4">{{ $product->sku }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            <p>Sell: ₦{{ number_format($product->selling_price, 2) }}</p>
                            <p>Buy: ₦{{ number_format($product->purchase_price, 2) }}</p>
                        </td>
                        <td class="px-6 py-4">{{ number_format($product->stock_quantity) }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$product->status" /></td>
                        <td class="px-6 py-4 text-right">
                            @if (! auth()->user()->hasRole(\App\Models\User::ROLE_SALES_OFFICER))
                                <div class="flex justify-end gap-3">
                                    <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('products.edit', $product) }}">Edit</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-600 hover:text-rose-800" type="submit">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-6 text-center text-slate-500">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $products->links() }}
</x-app-layout>
