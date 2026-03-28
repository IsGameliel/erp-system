<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasActiveAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->canUseApplication()) {
            return $next($request);
        }

        if ($request->routeIs('organizations.onboarding.*') || $request->routeIs('subscriptions.status') || $request->routeIs('subscriptions.pay') || $request->routeIs('logout') || $request->routeIs('profile.*')) {
            return $next($request);
        }

        if ($user->organization && ! $user->organization->setupCompleted()) {
            return redirect()->route('organizations.onboarding.show')->withErrors([
                'default' => 'Complete your organization onboarding before accessing the dashboard.',
            ]);
        }

        return redirect()->route('subscriptions.status')->withErrors([
            'default' => 'Your subscription is inactive. Contact the super admin to activate or renew access.',
        ]);
    }
}
