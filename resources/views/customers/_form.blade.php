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
        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
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

    @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
        <div>
            <label class="text-sm font-medium text-slate-700" for="discount_amount">Customer discount</label>
            <select class="form-select" id="discount_amount" name="discount_amount">
                <option value="">No saved discount</option>
                @foreach ($discountAmounts as $amount)
                    <option value="{{ $amount }}" @selected((string) old('discount_amount', $customer->discount_amount) === (string) $amount)>${{ number_format($amount, 2) }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-slate-500">Admin-controlled discount available to sales officers during checkout.</p>
        </div>
    @endif

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
</div>
