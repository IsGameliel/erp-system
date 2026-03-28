<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="name">Organization name</label>
        <input class="form-input" id="name" name="name" type="text" value="{{ old('name', $organization->name) }}" required>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="brand_name">Brand name</label>
        <input class="form-input" id="brand_name" name="brand_name" type="text" value="{{ old('brand_name', $organization->brand_name) }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="primary_domain">Primary domain</label>
        <input class="form-input" id="primary_domain" name="primary_domain" type="text" value="{{ old('primary_domain', $organization->primary_domain) }}" placeholder="www.thecompanyprice.com">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="contact_email">Contact email</label>
        <input class="form-input" id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $organization->contact_email) }}">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="contact_phone">Contact phone</label>
        <input class="form-input" id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $organization->contact_phone) }}">
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="address">Address</label>
        <textarea class="form-input min-h-28" id="address" name="address">{{ old('address', $organization->address) }}</textarea>
    </div>
</div>
