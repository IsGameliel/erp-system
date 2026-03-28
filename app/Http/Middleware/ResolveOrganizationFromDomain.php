<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganizationFromDomain
{
    public function __construct(private readonly TenantContext $tenantContext)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        if ($this->isOwnerHost($host)) {
            $this->tenantContext->set(null);

            return $next($request);
        }

        $organization = Organization::query()
            ->where('primary_domain', $host)
            ->orWhere('primary_domain', 'www.'.$host)
            ->first();

        $this->tenantContext->set($organization);

        return $next($request);
    }

    private function isOwnerHost(string $host): bool
    {
        return $host === strtolower((string) config('platform.owner_host'))
            || in_array($host, config('platform.local_hosts', []), true);
    }
}
