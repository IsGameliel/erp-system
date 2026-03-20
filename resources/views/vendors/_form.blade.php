<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="company_name">Company name</label>
        <input class="form-input" id="company_name" name="company_name" type="text" value="{{ old('company_name', $vendor->company_name) }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="contact_person">Contact person</label>
        <input class="form-input" id="contact_person" name="contact_person" type="text" value="{{ old('contact_person', $vendor->contact_person) }}">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
        <input class="form-input" id="email" name="email" type="email" value="{{ old('email', $vendor->email) }}">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="phone">Phone</label>
        <input class="form-input" id="phone" name="phone" type="text" value="{{ old('phone', $vendor->phone) }}">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="category">Category</label>
        <input class="form-input" id="category" name="category" type="text" value="{{ old('category', $vendor->category) }}">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="status">Status</label>
        <select class="form-select" id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $vendor->status ?: 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="address">Address</label>
        <textarea class="form-textarea" id="address" name="address">{{ old('address', $vendor->address) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="notes">Notes</label>
        <textarea class="form-textarea" id="notes" name="notes">{{ old('notes', $vendor->notes) }}</textarea>
    </div>
</div>
