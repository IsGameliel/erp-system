<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $ownerHost = strtolower((string) config('platform.owner_host'));
        $localHosts = config('platform.local_hosts', []);

        if ($host !== $ownerHost && ! in_array($host, $localHosts, true)) {
            abort(404);
        }

        return $next($request);
    }
}
