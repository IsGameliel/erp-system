@php
    $initialItems = old('items', isset($purchaseOrder) && $purchaseOrder->relationLoaded('items')
        ? $purchaseOrder->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit_cost' => $item->unit_cost,
        ])->toArray()
        : [['product_id' => '', 'quantity' => 1, 'unit_cost' => 0]]);

    $productOptions = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'purchase_price' => (float) $product->purchase_price,
    ])->values();
@endphp

<div
    x-data="purchaseOrderForm({
        products: {{ \Illuminate\Support\Js::from($productOptions) }},
        items: {{ \Illuminate\Support\Js::from($initialItems) }},
        tax: {{ (float) old('tax', $purchaseOrder->tax ?? 0) }},
        discount: {{ (float) old('discount', $purchaseOrder->discount ?? 0) }},
    })"
    class="space-y-6"
>
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div>
            <label class="text-sm font-medium text-slate-700" for="vendor_id">Vendor</label>
            <select class="form-select" id="vendor_id" name="vendor_id" required>
                <option value="">Select vendor</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $purchaseOrder->vendor_id) === (string) $vendor->id)>{{ $vendor->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="order_date">Order date</label>
            <input class="form-input" id="order_date" name="order_date" type="date" value="{{ old('order_date', optional($purchaseOrder->order_date)->format('Y-m-d') ?: $purchaseOrder->order_date) }}" required>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="status">Status</label>
            <select class="form-select" id="status" name="status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $purchaseOrder->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="tax">Tax</label>
            <input class="form-input" id="tax" name="tax" type="number" step="0.01" min="0" x-model="tax">
        </div>
    </div>

    <div class="page-panel !p-0">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-950">Purchase items</h3>
            <button class="btn-secondary" type="button" @click="addItem()">Add item</button>
        </div>
        <div class="space-y-4 p-6">
            <template x-for="(item, index) in items" :key="index">
                <div class="grid gap-4 rounded-2xl border border-slate-200 p-4 md:grid-cols-[2fr_1fr_1fr_auto]">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Product</label>
                        <select class="form-select" :name="`items[${index}][product_id]`" x-model="item.product_id" @change="syncUnitCost(index)" required>
                            <option value="">Select product</option>
                            <template x-for="product in products" :key="product.id">
                                <option :value="product.id" x-text="product.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Quantity</label>
                        <input class="form-input" :name="`items[${index}][quantity]`" type="number" min="1" x-model="item.quantity" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Unit cost</label>
                        <input class="form-input" :name="`items[${index}][unit_cost]`" type="number" min="0" step="0.01" x-model="item.unit_cost" required>
                    </div>
                    <div class="flex items-end">
                        <button class="rounded-full bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" type="button" @click="removeItem(index)">Remove</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label class="text-sm font-medium text-slate-700" for="notes">Notes</label>
            <textarea class="form-textarea" id="notes" name="notes">{{ old('notes', $purchaseOrder->notes) }}</textarea>
        </div>

        <div class="page-panel">
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between"><span class="text-slate-500">Subtotal</span><span class="font-semibold text-slate-900" x-text="currency(subtotal())"></span></div>
                <div>
                    <label class="text-sm font-medium text-slate-700" for="discount">Discount</label>
                    <input class="form-input" id="discount" name="discount" type="number" step="0.01" min="0" x-model="discount">
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-base">
                    <span class="font-semibold text-slate-700">Total</span>
                    <span class="text-xl font-semibold text-slate-950" x-text="currency(total())"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function purchaseOrderForm(config) {
        return {
            products: config.products,
            items: config.items,
            tax: config.tax,
            discount: config.discount,
            addItem() {
                this.items.push({ product_id: '', quantity: 1, unit_cost: 0 });
            },
            removeItem(index) {
                if (this.items.length === 1) {
                    return;
                }
                this.items.splice(index, 1);
            },
            syncUnitCost(index) {
                const product = this.products.find((entry) => String(entry.id) === String(this.items[index].product_id));
                if (product && (!this.items[index].unit_cost || Number(this.items[index].unit_cost) === 0)) {
                    this.items[index].unit_cost = product.purchase_price;
                }
            },
            subtotal() {
                return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_cost || 0)), 0);
            },
            total() {
                return Math.max(0, this.subtotal() + Number(this.tax || 0) - Number(this.discount || 0));
            },
            currency(value) {
                return `$${Number(value).toFixed(2)}`;
            }
        };
    }
</script>
