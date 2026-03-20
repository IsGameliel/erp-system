@php
    $selectedRole = old('role', $managedUser->role ?: \App\Models\User::ROLE_SALES_OFFICER);
    $selectedStoreId = old('store_id', $managedUser->store_id);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700" for="name">Full name</label>
        <input class="form-input" id="name" name="name" type="text" value="{{ old('name', $managedUser->name) }}" required>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
        <input class="form-input" id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" required>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="role">Role</label>
        <select class="form-select" id="role" name="role" required>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected($selectedRole === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="store_id">Store</label>
        <select class="form-select" id="store_id" name="store_id">
            <option value="">No store assigned</option>
            @foreach ($stores as $store)
                <option value="{{ $store->id }}" @selected((string) $selectedStoreId === (string) $store->id)>{{ $store->name }} ({{ $store->code }})</option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-slate-500">Only sales and procurement officers should be assigned to stores.</p>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="password">Password</label>
        <input class="form-input" id="password" name="password" type="password" {{ $managedUser->exists ? '' : 'required' }}>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="password_confirmation">Confirm password</label>
        <input class="form-input" id="password_confirmation" name="password_confirmation" type="password" {{ $managedUser->exists ? '' : 'required' }}>
    </div>
</div>
