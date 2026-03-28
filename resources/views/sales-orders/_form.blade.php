@php
    $initialItems = old('items', isset($salesOrder) && $salesOrder->relationLoaded('items')
        ? $salesOrder->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
        ])->toArray()
        : [['product_id' => '', 'quantity' => 1, 'unit_price' => 0]]);

    $productOptions = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'stock_quantity' => (int) $product->stock_quantity,
        'selling_price' => (float) $product->selling_price,
    ])->values();

    $customerOptions = $customers->map(fn ($customer) => [
        'id' => $customer->id,
        'full_name' => $customer->full_name,
        'product_discounts' => $customer->productDiscounts
            ->map(fn ($discount) => [
                'product_id' => (string) $discount->product_id,
                'store_id' => (string) $discount->store_id,
                'discount_amount' => (float) $discount->discount_amount,
            ])->values()->all(),
    ])->values();

    $selectedCustomerId = old('customer_id', $salesOrder->customer_id);
    $customerMode = old('customer_mode', $selectedCustomerId ? 'existing' : 'new');
    $selectedPaymentStatus = old('payment_status', $salesOrder->payment_status ?: \App\Models\SalesOrder::PAYMENT_STATUS_PAID);
    $amountPaid = old('amount_paid', $salesOrder->amount_paid ?? ($selectedPaymentStatus === \App\Models\SalesOrder::PAYMENT_STATUS_PAID ? $salesOrder->total ?? 0 : 0));
    $salesOfficerLimitedEdit = isset($salesOrder) && $salesOrder->exists && auth()->user()->hasRole(\App\Models\User::ROLE_SALES_OFFICER);
@endphp

<div
    x-data="salesOrderForm({
        products: {{ \Illuminate\Support\Js::from($productOptions) }},
        customers: {{ \Illuminate\Support\Js::from($customerOptions) }},
        items: {{ \Illuminate\Support\Js::from($initialItems) }},
        tax: {{ (float) old('tax', $salesOrder->tax ?? 0) }},
        discount: {{ (float) old('discount', $salesOrder->discount ?? 0) }},
        amountPaid: '{{ $amountPaid }}',
        customerMode: '{{ $customerMode }}',
        selectedCustomerId: '{{ $selectedCustomerId }}',
        selectedCustomerLabel: @js($customers->firstWhere('id', (int) $selectedCustomerId)?->full_name ?? ''),
        selectedStoreId: '{{ old('store_id', $salesOrder->store_id ?? auth()->user()?->store_id) }}',
        paymentStatus: '{{ $selectedPaymentStatus }}',
        allowManualDiscount: {{ auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN) ? 'true' : 'false' }},
    })"
    class="space-y-6"
>
    <div class="page-panel space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-950">Customer information</h3>
                <p class="mt-1 text-sm text-slate-500">Pick an existing customer or register a new one during checkout.</p>
            </div>
            <div class="flex rounded-full bg-slate-100 p-1 text-sm">
                <button class="rounded-full px-4 py-2" :class="customerMode === 'existing' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" type="button" @click="customerMode = 'existing'" @disabled($salesOfficerLimitedEdit)>Existing</button>
                <button class="rounded-full px-4 py-2" :class="customerMode === 'new' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" type="button" @click="customerMode = 'new'" @disabled($salesOfficerLimitedEdit)>New</button>
            </div>
        </div>

        <input type="hidden" name="customer_mode" x-model="customerMode">

        <div x-show="customerMode === 'existing'" class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="text-sm font-medium text-slate-700" for="customer_search">Search customer</label>
                <input
                    class="form-input"
                    id="customer_search"
                    type="text"
                    x-model="customerSearchQuery"
                    @input="selectedCustomerId = ''"
                    placeholder="Type a customer name"
                    :disabled="customerMode !== 'existing'"
                    @disabled($salesOfficerLimitedEdit)
                >
                <p class="mt-1 text-xs text-slate-500">Search by customer name, then select from the filtered list below.</p>
            </div>
            <div class="md:col-span-1 xl:col-span-3">
                <label class="text-sm font-medium text-slate-700" for="customer_id">Customer</label>
                <select class="form-select" id="customer_id" name="customer_id" x-model="selectedCustomerId" :required="customerMode === 'existing'" :disabled="customerMode !== 'existing'" @disabled($salesOfficerLimitedEdit)>
                    <option value="">Select customer</option>
                    <template x-for="customer in filteredCustomers()" :key="customer.id">
                        <option :value="customer.id" x-text="customerOptionLabel(customer)"></option>
                    </template>
                    <option x-show="filteredCustomers().length === 0" disabled value="">No matching customers found</option>
                </select>
            </div>
            <div class="md:col-span-2 xl:col-span-3" x-show="savedDiscount() > 0">
                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm text-cyan-900">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <p>Saved discount for the selected items: <span class="font-semibold" x-text="currency(savedDiscount())"></span></p>
                        @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                            <button class="btn-secondary" type="button" @click="applySavedDiscount()">Apply saved discount</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div x-show="customerMode === 'new'" class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-slate-700" for="customer_full_name">Full name</label>
                <input class="form-input" id="customer_full_name" name="customer[full_name]" type="text" value="{{ old('customer.full_name') }}" :required="customerMode === 'new'" :disabled="customerMode !== 'new'" @disabled($salesOfficerLimitedEdit)>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700" for="customer_phone">Phone</label>
                <input class="form-input" id="customer_phone" name="customer[phone]" type="text" value="{{ old('customer.phone') }}" :disabled="customerMode !== 'new'" @disabled($salesOfficerLimitedEdit)>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700" for="customer_email">Email (optional)</label>
                <input class="form-input" id="customer_email" name="customer[email]" type="email" value="{{ old('customer.email') }}" :disabled="customerMode !== 'new'" @disabled($salesOfficerLimitedEdit)>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700" for="customer_business_name">Business name</label>
                <input class="form-input" id="customer_business_name" name="customer[business_name]" type="text" value="{{ old('customer.business_name') }}" :disabled="customerMode !== 'new'" @disabled($salesOfficerLimitedEdit)>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700" for="customer_type">Customer type</label>
                <input class="form-input" id="customer_type" name="customer[customer_type]" type="text" value="{{ old('customer.customer_type') }}" :disabled="customerMode !== 'new'" @disabled($salesOfficerLimitedEdit)>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-700" for="customer_address">Address</label>
                <textarea class="form-textarea" id="customer_address" name="customer[address]" :disabled="customerMode !== 'new'" @disabled($salesOfficerLimitedEdit)>{{ old('customer.address') }}</textarea>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div>
            <label class="text-sm font-medium text-slate-700" for="order_date">Order date</label>
            <input class="form-input" id="order_date" name="order_date" type="date" value="{{ old('order_date', optional($salesOrder->order_date)->format('Y-m-d') ?: $salesOrder->order_date) }}" required @disabled($salesOfficerLimitedEdit)>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="status">Status</label>
            <select class="form-select" id="status" name="status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $salesOrder->status ?: \App\Models\SalesOrder::STATUS_DELIVERED) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="payment_status">Payment type</label>
            <select class="form-select" id="payment_status" name="payment_status" x-model="paymentStatus" required>
                @foreach ($paymentStatuses as $paymentStatus)
                    <option value="{{ $paymentStatus }}" @selected($selectedPaymentStatus === $paymentStatus)>{{ $paymentStatus === \App\Models\SalesOrder::PAYMENT_STATUS_PAID ? 'Paid now' : 'Credit / Pay later' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="tax">Tax</label>
            <input class="form-input" id="tax" name="tax" type="number" step="0.01" min="0" x-model="tax" @disabled($salesOfficerLimitedEdit)>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="payment_method">Payment method</label>
            <select class="form-select" id="payment_method" name="payment_method" :disabled="paymentStatus !== 'paid'" :required="paymentStatus === 'paid'">
                <option value="">Select payment method</option>
                @foreach ($paymentMethods as $paymentMethod)
                    <option value="{{ $paymentMethod }}" @selected(old('payment_method', $salesOrder->payment_method) === $paymentMethod)>{{ strtoupper($paymentMethod) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="due_date">Due date</label>
            <input class="form-input" id="due_date" name="due_date" type="date" value="{{ old('due_date', optional($salesOrder->due_date)->format('Y-m-d')) }}" :disabled="paymentStatus !== 'pending'" :required="paymentStatus === 'pending'">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700" for="amount_paid">Amount paid</label>
            <input class="form-input" id="amount_paid" name="amount_paid" type="number" min="0" step="0.01" x-model="amountPaid" :disabled="paymentStatus !== 'paid'" placeholder="Leave blank to use total" @disabled($salesOfficerLimitedEdit)>
            <p class="mt-1 text-xs text-slate-500">Enter the cash received. Leave blank to record the full total as paid.</p>
        </div>
        <div>
            @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                <label class="text-sm font-medium text-slate-700" for="store_id">Store</label>
                <select class="form-select" id="store_id" name="store_id" x-model="selectedStoreId" @disabled($salesOfficerLimitedEdit)>
                    <option value="">Select store</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}" @selected((string) old('store_id', $salesOrder->store_id) === (string) $store->id)>{{ $store->name }}</option>
                    @endforeach
                </select>
            @else
                <label class="text-sm font-medium text-slate-700">Store</label>
                <div class="form-input flex items-center bg-slate-50 text-slate-600">{{ auth()->user()->store?->name ?? 'No store assigned' }}</div>
            @endif
        </div>
    </div>

    <div class="page-panel !p-0">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-950">Order items</h3>
            <button class="btn-secondary" type="button" @click="addItem()" @disabled($salesOfficerLimitedEdit)>Add item</button>
        </div>
        <div class="space-y-4 p-6">
            <template x-for="(item, index) in items" :key="index">
                <div class="grid gap-4 rounded-2xl border border-slate-200 p-4 md:grid-cols-[2fr_1fr_1fr_auto]">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Product</label>
                        <select class="form-select" :name="`items[${index}][product_id]`" x-model="item.product_id" @change="syncUnitPrice(index)" required @disabled($salesOfficerLimitedEdit)>
                            <option value="">Select product</option>
                            <template x-for="product in products" :key="product.id">
                                <option :value="product.id" x-text="`${product.name} (Stock: ${product.stock_quantity})`"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Quantity</label>
                        <input class="form-input" :name="`items[${index}][quantity]`" type="number" min="1" x-model="item.quantity" required @disabled($salesOfficerLimitedEdit)>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Unit price</label>
                        <input class="form-input bg-slate-50 text-slate-600" :name="`items[${index}][unit_price]`" type="number" min="0" step="0.01" x-model="item.unit_price" readonly required @disabled($salesOfficerLimitedEdit)>
                        <p class="mt-1 text-xs text-slate-500">Unit price is auto-filled from the selected product.</p>
                    </div>
                    <div class="flex items-end">
                        <button class="rounded-full bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" type="button" @click="removeItem(index)" @disabled($salesOfficerLimitedEdit)>Remove</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label class="text-sm font-medium text-slate-700" for="notes">Notes</label>
            <textarea class="form-textarea" id="notes" name="notes" @disabled($salesOfficerLimitedEdit)>{{ old('notes', $salesOrder->notes) }}</textarea>
        </div>

        <div class="page-panel">
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between"><span class="text-slate-500">Subtotal</span><span class="font-semibold text-slate-900" x-text="currency(subtotal())"></span></div>
                @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                    <div>
                        <label class="text-sm font-medium text-slate-700" for="discount">Discount</label>
                        <input class="form-input" id="discount" name="discount" type="number" step="0.01" min="0" x-model="discount" @disabled($salesOfficerLimitedEdit)>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Discount</span>
                        <span class="font-semibold text-slate-900" x-text="currency(savedDiscount())"></span>
                    </div>
                @endif
                <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600" x-show="paymentStatus === 'pending'">
                    This order is being recorded as a credit sale and can be paid later.
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Amount paid</span>
                    <span class="font-semibold text-slate-900" x-text="currency(recordedAmountPaid())"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Balance impact</span>
                    <span class="font-semibold text-slate-900" x-text="signedCurrency(balanceImpact())"></span>
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
    function salesOrderForm(config) {
        return {
            products: config.products,
            customers: config.customers,
            items: config.items,
            tax: config.tax,
            discount: config.discount,
            amountPaid: config.amountPaid,
            customerMode: config.customerMode,
            selectedCustomerId: config.selectedCustomerId,
            customerSearchQuery: config.selectedCustomerLabel,
            selectedStoreId: config.selectedStoreId,
            paymentStatus: config.paymentStatus,
            allowManualDiscount: config.allowManualDiscount,
            filteredCustomers() {
                const query = this.customerSearchQuery.trim().toLowerCase();

                if (!query) {
                    return this.customers;
                }

                return this.customers.filter((customer) => customer.full_name.toLowerCase().includes(query));
            },
            customerOptionLabel(customer) {
                return `${customer.full_name}${(customer.product_discounts || []).length > 0 ? ' - Product discounts available' : ''}`;
            },
            recordedAmountPaid() {
                if (this.paymentStatus !== 'paid') {
                    return 0;
                }

                return this.amountPaid === '' ? this.total() : Number(this.amountPaid || 0);
            },
            addItem() {
                this.items.push({ product_id: '', quantity: 1, unit_price: 0 });
            },
            removeItem(index) {
                if (this.items.length === 1) {
                    return;
                }
                this.items.splice(index, 1);
            },
            syncUnitPrice(index) {
                const product = this.products.find((entry) => String(entry.id) === String(this.items[index].product_id));
                if (product) {
                    this.items[index].unit_price = product.selling_price;
                }
            },
            subtotal() {
                return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0);
            },
            customerDiscounts() {
                const customer = this.customers.find((entry) => String(entry.id) === String(this.selectedCustomerId));
                return customer?.product_discounts || [];
            },
            savedDiscount() {
                return this.items.reduce((sum, item) => {
                    const discount = this.customerDiscounts().find((entry) => String(entry.product_id) === String(item.product_id) && String(entry.store_id) === String(this.selectedStoreId));
                    const amount = Number(discount?.discount_amount || 0);

                    return sum + (amount * Number(item.quantity || 0));
                }, 0);
            },
            applySavedDiscount() {
                this.discount = this.savedDiscount();
            },
            total() {
                const discount = this.allowManualDiscount ? Number(this.discount || 0) : this.savedDiscount();

                return Math.max(0, this.subtotal() + Number(this.tax || 0) - discount);
            },
            balanceImpact() {
                return this.recordedAmountPaid() - this.total();
            },
            currency(value) {
                return `₦${Number(value).toFixed(2)}`;
            },
            signedCurrency(value) {
                const amount = Number(value);
                const absoluteAmount = Math.abs(amount);

                if (amount < 0) {
                    return `-${this.currency(absoluteAmount)}`;
                }

                return `${amount > 0 ? '+' : ''}${this.currency(absoluteAmount)}`;
            }
        };
    }
</script>
