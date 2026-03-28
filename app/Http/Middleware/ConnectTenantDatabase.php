<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use App\Support\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConnectTenantDatabase
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly TenantDatabaseManager $tenantDatabaseManager,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $organization = $request->user()?->organization ?: $this->tenantContext->get();

        if ($request->user() && ! $request->user()->isSuperAdmin() && ! $this->tenantDatabaseManager->hasConfiguration($organization)) {
            if (! $request->routeIs('organizations.onboarding.*') && ! $request->routeIs('subscriptions.*') && ! $request->routeIs('logout') && ! $request->routeIs('profile.*')) {
                return redirect()->route('organizations.onboarding.show')->withErrors([
                    'default' => 'Complete your organization database setup before accessing tenant data.',
                ]);
            }
        }

        $this->tenantDatabaseManager->configure($organization);

        return $next($request);
    }
}
