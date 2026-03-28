<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Organization Onboarding</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Set up your company workspace</h2>
            </div>
            <p class="text-sm text-slate-500">Complete this once with your brand and database details, then continue to subscription payment.</p>
        </div>
    </x-slot>

    <form action="{{ route('organizations.onboarding.update') }}" class="page-panel space-y-6" method="POST">
        @csrf

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-slate-700" for="name">Organization name</label>
                <input class="form-input" id="name" name="name" type="text" value="{{ old('name', $organization?->name) }}" required>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700" for="brand_name">Brand name</label>
                <input class="form-input" id="brand_name" name="brand_name" type="text" value="{{ old('brand_name', $organization?->brand_name) }}" required>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700" for="contact_email">Contact email</label>
                <input class="form-input" id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $organization?->contact_email) }}" required>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700" for="contact_phone">Contact phone</label>
                <input class="form-input" id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $organization?->contact_phone) }}">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-700" for="address">Address</label>
                <textarea class="form-input min-h-28" id="address" name="address">{{ old('address', $organization?->address) }}</textarea>
            </div>
        </div>

        <div class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-950">Database setup</h3>
                <p class="mt-1 text-sm text-slate-500">Provide the database details for your organization workspace. The connection is validated before onboarding completes.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700" for="db_connection">Database driver</label>
                    <select class="form-select" id="db_connection" name="db_connection" required>
                        <option value="mysql" @selected(old('db_connection', $organization?->db_connection ?? 'mysql') === 'mysql')>MySQL</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="db_host">Database host</label>
                    <input class="form-input" id="db_host" name="db_host" type="text" value="{{ old('db_host', $organization?->db_host) }}" required>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="db_port">Database port</label>
                    <input class="form-input" id="db_port" name="db_port" type="number" value="{{ old('db_port', $organization?->db_port ?? 3306) }}" required>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="db_database">Database name</label>
                    <input class="form-input" id="db_database" name="db_database" type="text" value="{{ old('db_database', $organization?->db_database) }}" required>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="db_username">Database username</label>
                    <input class="form-input" id="db_username" name="db_username" type="text" value="{{ old('db_username', $organization?->db_username) }}" required>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="db_password">Database password</label>
                    <input class="form-input" id="db_password" name="db_password" type="password" value="{{ old('db_password') }}">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button class="btn-primary" type="submit">Save and continue to subscription</button>
        </div>
    </form>
</x-app-layout>
