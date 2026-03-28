<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-700">First Run Setup</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950">Configure {{ $applicationBrandName }}</h1>
            <p class="mt-2 text-sm text-slate-500">Set your brand name, connect the database, and create the first super admin account.</p>
        </div>

        <x-alert />

        <form action="{{ route('onboarding.store') }}" class="space-y-6" method="POST">
            @csrf

            <div class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Brand</h2>
                    <p class="text-sm text-slate-500">This name appears across the dashboard once installation is complete.</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="brand_name">Brand name</label>
                    <input class="form-input mt-2" id="brand_name" name="brand_name" type="text" value="{{ old('brand_name', 'ERP System') }}" required>
                </div>
            </div>

            <div class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Database</h2>
                    <p class="text-sm text-slate-500">MySQL is expected here. The database itself should already exist before you submit this form.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700" for="db_host">Database host</label>
                        <input class="form-input mt-2" id="db_host" name="db_host" type="text" value="{{ old('db_host', '127.0.0.1') }}" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="db_port">Database port</label>
                        <input class="form-input mt-2" id="db_port" name="db_port" type="number" value="{{ old('db_port', '3306') }}" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="db_database">Database name</label>
                        <input class="form-input mt-2" id="db_database" name="db_database" type="text" value="{{ old('db_database') }}" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="db_username">Database username</label>
                        <input class="form-input mt-2" id="db_username" name="db_username" type="text" value="{{ old('db_username') }}" required>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="db_password">Database password</label>
                    <input class="form-input mt-2" id="db_password" name="db_password" type="password" value="{{ old('db_password') }}">
                </div>
            </div>

            <div class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Owner Account</h2>
                    <p class="text-sm text-slate-500">This is your super admin account. It controls users, plans, payments, and subscription approvals.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700" for="owner_name">Full name</label>
                        <input class="form-input mt-2" id="owner_name" name="owner_name" type="text" value="{{ old('owner_name') }}" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="owner_email">Email address</label>
                        <input class="form-input mt-2" id="owner_email" name="owner_email" type="email" value="{{ old('owner_email') }}" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="owner_password">Password</label>
                        <input class="form-input mt-2" id="owner_password" name="owner_password" type="password" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="owner_password_confirmation">Confirm password</label>
                        <input class="form-input mt-2" id="owner_password_confirmation" name="owner_password_confirmation" type="password" required>
                    </div>
                </div>
            </div>

            <button class="btn-primary w-full justify-center" type="submit">Complete onboarding</button>
        </form>
    </div>
</x-guest-layout>
