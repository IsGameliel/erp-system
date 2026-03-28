<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\ResolveOrganizationFromDomain::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'installed' => \App\Http\Middleware\EnsureApplicationIsInstalled::class,
            'guest.install' => \App\Http\Middleware\RedirectIfApplicationIsInstalled::class,
            'active.access' => \App\Http\Middleware\EnsureUserHasActiveAccess::class,
            'owner.host' => \App\Http\Middleware\EnsureOwnerHost::class,
            'tenant.domain' => \App\Http\Middleware\EnsureTenantDomainAccess::class,
            'tenant.db' => \App\Http\Middleware\ConnectTenantDatabase::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
