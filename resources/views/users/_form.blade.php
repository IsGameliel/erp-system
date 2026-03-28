@php
    $selectedRole = old('role', $managedUser->role ?: \App\Models\User::ROLE_SALES_OFFICER);
    $selectedOrganizationId = old('organization_id', $managedUser->organization_id);
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

    @if (auth()->user()?->isSuperAdmin())
        <div>
            <label class="text-sm font-medium text-slate-700" for="organization_id">Organization</label>
            <select class="form-select" id="organization_id" name="organization_id">
                <option value="">No organization assigned</option>
                @foreach ($organizations as $organization)
                    <option value="{{ $organization->id }}" @selected((string) $selectedOrganizationId === (string) $organization->id)>{{ $organization->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if (request()->routeIs('owner.*'))
        <div class="md:col-span-2">
            <p class="text-sm font-medium text-slate-700">Store assignment</p>
            <p class="mt-2 text-sm text-slate-500">Store assignment is managed inside the organization workspace after the user signs in there.</p>
        </div>
    @else
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
    @endif

    @if (auth()->user()?->isSuperAdmin())
        <div>
            <label class="text-sm font-medium text-slate-700" for="access_expires_at">Paid through</label>
            <input class="form-input" id="access_expires_at" name="access_expires_at" type="date" value="{{ old('access_expires_at', optional($managedUser->access_expires_at)->format('Y-m-d')) }}">
            <p class="mt-2 text-xs text-slate-500">Leave blank for unlimited access. Expired users will be blocked at login.</p>
        </div>

        <div class="flex items-center gap-3 self-end rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <input class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-500" id="access_enabled" name="access_enabled" type="checkbox" value="1" @checked(old('access_enabled', $managedUser->exists ? $managedUser->access_enabled : false))>
            <label class="text-sm font-medium text-slate-700" for="access_enabled">Allow this user to access the application immediately</label>
        </div>
    @endif

    <div>
        <label class="text-sm font-medium text-slate-700" for="password">Password</label>
        <input class="form-input" id="password" name="password" type="password" {{ $managedUser->exists ? '' : 'required' }}>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700" for="password_confirmation">Confirm password</label>
        <input class="form-input" id="password_confirmation" name="password_confirmation" type="password" {{ $managedUser->exists ? '' : 'required' }}>
    </div>
</div>
