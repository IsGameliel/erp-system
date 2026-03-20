<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-semibold tracking-tight text-slate-950">{{ $product->name }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $product->sku }}</p>
            </div>
            <a href="{{ route('products.edit', $product) }}" class="btn-primary">Edit product</a>
        </div>
    </x-slot>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="page-panel">
            <h3 class="text-lg font-semibold text-slate-950">Product details</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Selling price</dt><dd class="font-medium text-slate-900">${{ number_format($product->selling_price, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Purchase price</dt><dd class="font-medium text-slate-900">${{ number_format($product->purchase_price, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Stock quantity</dt><dd class="font-medium text-slate-900">{{ number_format($product->stock_quantity) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-status-badge :status="$product->status" /></dd></div>
            </dl>
            <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                {{ $product->description ?: 'No description available.' }}
            </div>
        </div>

        <div class="page-panel">
            <h3 class="text-lg font-semibold text-slate-950">Usage summary</h3>
            <div class="mt-4 space-y-3 text-sm text-slate-600">
                <p>Sales order lines: <span class="font-medium text-slate-900">{{ $product->salesOrderItems->count() }}</span></p>
                <p>Purchase order lines: <span class="font-medium text-slate-900">{{ $product->purchaseOrderItems->count() }}</span></p>
            </div>
        </div>
    </div>
</x-app-layout>
