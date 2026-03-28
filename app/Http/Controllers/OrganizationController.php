<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $organizations = Organization::query()
            ->withCount('users')
            ->with('currentSubscriptionPlan')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('brand_name', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('organizations.index', [
            'organizations' => $organizations,
        ]);
    }

    public function create()
    {
        return view('organizations.create', [
            'organization' => new Organization(),
        ]);
    }

    public function store(Request $request)
    {
        $organization = Organization::create($this->validatedData($request));

        $this->activityLogService->log($request->user()?->id, 'created', 'organizations', "Created organization {$organization->name}.", $organization);

        return redirect()->route('owner.organizations.index')->with('success', 'Organization created successfully.');
    }

    public function edit(Organization $organization)
    {
        return view('organizations.edit', [
            'organization' => $organization,
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $organization->update($this->validatedData($request));

        $this->activityLogService->log($request->user()?->id, 'updated', 'organizations', "Updated organization {$organization->name}.", $organization);

        return redirect()->route('owner.organizations.index')->with('success', 'Organization updated successfully.');
    }

    public function destroy(Request $request, Organization $organization)
    {
        $name = $organization->name;
        $organization->delete();

        $this->activityLogService->log($request->user()?->id, 'deleted', 'organizations', "Deleted organization {$name}.");

        return redirect()->route('owner.organizations.index')->with('success', 'Organization deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        /** @var \App\Models\Organization|null $organization */
        $organization = $request->route('organization');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'primary_domain' => ['nullable', 'string', 'max:255', Rule::unique(Organization::class, 'primary_domain')->ignore($organization?->id)],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        if (filled($data['primary_domain'] ?? null)) {
            $domain = Str::lower((string) $data['primary_domain']);
            $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
            $data['primary_domain'] = rtrim($domain, '/');
        }

        return $data;
    }
}
