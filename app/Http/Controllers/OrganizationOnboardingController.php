<?php

namespace App\Http\Controllers;

use App\Support\TenantDatabaseManager;
use Illuminate\Http\Request;

class OrganizationOnboardingController extends Controller
{
    public function __construct(
        private readonly TenantDatabaseManager $tenantDatabaseManager,
    ) {
    }

    public function show(Request $request)
    {
        abort_if($request->user()?->isSuperAdmin(), 403);

        return view('organizations.onboarding', [
            'organization' => $request->user()?->organization,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        abort_if($user?->isSuperAdmin(), 403);

        $organization = $user?->organization;
        abort_if(! $organization, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'db_connection' => ['required', 'in:mysql'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank($data['db_password'] ?? null)) {
            $data['db_password'] = $organization->db_password;
        }

        if (blank($data['db_password'] ?? null)) {
            return back()
                ->withInput($request->except('db_password'))
                ->withErrors([
                    'db_password' => 'Enter the database password for this organization.',
                ]);
        }

        $this->tenantDatabaseManager->validateConnection($data);
        $this->tenantDatabaseManager->migrate($data);

        $organization->update([
            ...$data,
            'setup_completed_at' => now(),
        ]);

        return redirect()->route('subscriptions.status')->with('success', 'Organization setup completed. Select a subscription plan and submit your payment next.');
    }
}
