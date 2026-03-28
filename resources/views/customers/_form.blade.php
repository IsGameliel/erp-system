@php
    $savedProductDiscounts = collect(old('product_discounts', $customer->relationLoaded('productDiscounts')
        ? $customer->productDiscounts->map(fn ($discount) => [
            'product_id' => $discount->product_id,
            'store_id' => $discount->store_id,
            'discount_amount' => $discount->discount_amount,
        ])->all()
        : []))
        ->mapWithKeys(fn ($discount) => [
            (string) ($discount['product_id'] ?? '') => [
                'store_id' => (string) ($discount['store_id'] ?? ''),
                'discount_amount' => (string) ($discount['discount_amount'] ?? ''),
            ],
        ])
        ->all();
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="full_name">Full name</label>
        <input class="form-input" id="full_name" name="full_name" type="text" value="{{ old('full_name', $customer->full_name) }}" required>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="business_name">Business name</label>
        <input class="form-input" id="business_name" name="business_name" type="text" value="{{ old('business_name', $customer->business_name) }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="email">Email (optional)</label>
        <input class="form-input" id="email" name="email" type="email" value="{{ old('email', $customer->email) }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="phone">Phone</label>
        <input class="form-input" id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone) }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="customer_type">Customer type</label>
        <input class="form-input" id="customer_type" name="customer_type" type="text" value="{{ old('customer_type', $customer->customer_type) }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="status">Status</label>
        <select class="form-select" id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $customer->status ?: 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="address">Address</label>
        <textarea class="form-textarea" id="address" name="address">{{ old('address', $customer->address) }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="notes">Notes</label>
        <textarea class="form-textarea" id="notes" name="notes">{{ old('notes', $customer->notes) }}</textarea>
    </div>

    @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
        <div class="md:col-span-2">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Product-specific discounts</h3>
                    <p class="mt-1 text-sm text-slate-500">Choose the product discount and the store where it applies. Sales officers can use these saved discounts during checkout, but cannot create or edit them.</p>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($products as $product)
                        <label class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 md:flex-row md:items-center md:justify-between">
                            <div class="flex items-start gap-3">
                                <input
                                    class="mt-1 rounded border-slate-300 text-cyan-600 shadow-sm focus:ring-cyan-500"
                                    type="checkbox"
                                    name="product_discounts[{{ $product->id }}][enabled]"
                                    value="1"
                                    @checked(array_key_exists((string) $product->id, $savedProductDiscounts))
                                >
                                <div>
                                    <p class="font-medium text-slate-900">{{ $product->name }}</p>
                                    <p class="text-sm text-slate-500">Current selling price: ₦{{ number_format($product->selling_price, 2) }}</p>
                                </div>
                            </div>

                            <div class="w-full md:w-48">
                                <input type="hidden" name="product_discounts[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                                <label class="text-sm font-medium text-slate-700" for="product_discount_store_{{ $product->id }}">Store</label>
                                <select
                                    class="form-select mt-2"
                                    id="product_discount_store_{{ $product->id }}"
                                    name="product_discounts[{{ $product->id }}][store_id]"
                                >
                                    <option value="">Select store</option>
                                    @foreach ($stores as $store)
                                        <option
                                            value="{{ $store->id }}"
                                            @selected(($savedProductDiscounts[(string) $product->id]['store_id'] ?? '') === (string) $store->id)
                                        >
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full md:w-48">
                                <label class="text-sm font-medium text-slate-700" for="product_discount_{{ $product->id }}">Discount amount</label>
                                <input
                                    class="form-input mt-2"
                                    id="product_discount_{{ $product->id }}"
                                    name="product_discounts[{{ $product->id }}][discount_amount]"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    value="{{ $savedProductDiscounts[(string) $product->id]['discount_amount'] ?? '' }}"
                                    placeholder="0.00"
                                >
                            </div>
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">Create products first before assigning customer discounts.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
