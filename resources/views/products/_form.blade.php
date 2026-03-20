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
</div>
