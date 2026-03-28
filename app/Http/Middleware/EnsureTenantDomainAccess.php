<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantDomainAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $domain = strtolower((string) ($user->organization?->primary_domain));

        if ($domain === '') {
            return $next($request);
        }

        $currentHost = strtolower($request->getHost());

        if (in_array($currentHost, config('platform.local_hosts', []), true)) {
            return $next($request);
        }

        if ($currentHost === $domain) {
            return $next($request);
        }

        return redirect()->to($request->getScheme().'://'.$domain.$request->getRequestUri());
    }
}
