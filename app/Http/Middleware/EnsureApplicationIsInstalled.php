<?php

namespace App\Http\Middleware;

use App\Support\InstallationManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationIsInstalled
{
    public function __construct(private readonly InstallationManager $installationManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->installationManager->installed() && ! $request->routeIs('onboarding.*')) {
            return redirect()->route('onboarding.create');
        }

        return $next($request);
    }
}
