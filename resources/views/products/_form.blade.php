@php
    $savedStoreQuantities = collect(old('store_quantities', isset($product) && $product->relationLoaded('storeQuantities')
        ? $product->storeQuantities->map(fn ($quantity) => [
            'store_id' => $quantity->store_id,
            'quantity' => $quantity->quantity,
        ])->toArray()
        : []))
        ->mapWithKeys(fn ($entry) => [(string) ($entry['store_id'] ?? '') => (string) ($entry['quantity'] ?? 0)]);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="name">Name</label>
        <input class="form-input" id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="sku">SKU</label>
        <input class="form-input" id="sku" name="sku" type="text" value="{{ old('sku', $product->sku) }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="category_id">Category</label>
        <select class="form-select" id="category_id" name="category_id" @disabled(! $productCategoriesAvailable)>
            <option value="">Uncategorized</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @if (! $productCategoriesAvailable)
            <p class="mt-2 text-sm text-amber-700">Product categories are unavailable until the latest tenant database migration is applied.</p>
        @endif
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="selling_price">Selling price</label>
        <input class="form-input" id="selling_price" name="selling_price" type="number" min="0" step="0.01" value="{{ old('selling_price', $product->selling_price) }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="purchase_price">Purchase price</label>
        <input class="form-input" id="purchase_price" name="purchase_price" type="number" min="0" step="0.01" value="{{ old('purchase_price', $product->purchase_price) }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="stock_quantity">Stock quantity</label>
        <input class="form-input" id="stock_quantity" name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
        <p class="mt-1 text-xs text-slate-500">Total stock across all assigned stores.</p>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="status">Status</label>
        <select class="form-select" id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $product->status ?: 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="description">Description</label>
        <textarea class="form-textarea" id="description" name="description">{{ old('description', $product->description) }}</textarea>
    </div>

    @isset($stores)
        <div class="md:col-span-2">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <h3 class="text-lg font-semibold text-slate-950">Store quantity assignment</h3>
                <p class="mt-1 text-sm text-slate-500">Assign available quantity to each store. The sum of all store quantities cannot exceed the total stock quantity above.</p>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @forelse ($stores as $store)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <input type="hidden" name="store_quantities[{{ $store->id }}][store_id]" value="{{ $store->id }}">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $store->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $store->code }}{{ $store->location ? ' • '.$store->location : '' }}</p>
                                </div>
                                <div class="w-32">
                                    <label class="text-sm font-medium text-slate-700" for="store_quantity_{{ $store->id }}">Qty</label>
                                    <input class="form-input mt-2" id="store_quantity_{{ $store->id }}" name="store_quantities[{{ $store->id }}][quantity]" type="number" min="0" value="{{ $savedStoreQuantities[(string) $store->id] ?? 0 }}">
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No stores available yet. Create stores before assigning product quantities.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endisset
</div>
