<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="name">Plan name</label>
        <input class="form-input" id="name" name="name" type="text" value="{{ old('name', $plan->name) }}" required>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="price">Price</label>
        <input class="form-input" id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $plan->price) }}" required>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="duration_months">Duration in months</label>
        <input class="form-input" id="duration_months" name="duration_months" type="number" min="1" max="60" value="{{ old('duration_months', $plan->duration_months ?: 12) }}" required>
    </div>

    <div class="flex items-center gap-3 self-end rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
        <input class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-500" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $plan->exists ? $plan->is_active : true))>
        <label class="text-sm font-medium text-slate-700" for="is_active">Plan is available for payment</label>
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700" for="description">Description</label>
        <textarea class="form-input min-h-28" id="description" name="description">{{ old('description', $plan->description) }}</textarea>
    </div>
</div>
