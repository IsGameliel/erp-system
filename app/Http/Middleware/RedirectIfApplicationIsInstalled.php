<?php

namespace App\Http\Middleware;

use App\Support\InstallationManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfApplicationIsInstalled
{
    public function __construct(private readonly InstallationManager $installationManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->installationManager->installed()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
